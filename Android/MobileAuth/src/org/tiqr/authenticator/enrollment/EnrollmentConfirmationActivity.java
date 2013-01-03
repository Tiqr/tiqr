package org.tiqr.authenticator.enrollment;

import java.net.MalformedURLException;
import java.net.URL;
import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

import javax.crypto.SecretKey;

import org.apache.http.HttpResponse;
import org.apache.http.NameValuePair;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.message.BasicNameValuePair;
import org.apache.http.protocol.HTTP;
import org.apache.http.util.EntityUtils;
import org.json.JSONException;
import org.json.JSONObject;
import org.tiqr.authenticator.Config;
import org.tiqr.authenticator.NotificationRegistration;
import org.tiqr.authenticator.R;
import org.tiqr.authenticator.auth.EnrollmentChallenge;
import org.tiqr.authenticator.datamodel.DbAdapter;
import org.tiqr.authenticator.exceptions.SecurityFeaturesException;
import org.tiqr.authenticator.exceptions.UserException;
import org.tiqr.authenticator.general.AbstractActivityGroup;
import org.tiqr.authenticator.general.AbstractConfirmationActivity;
import org.tiqr.authenticator.security.Encryption;
import org.tiqr.authenticator.security.Secret;

import android.content.Intent;
import android.os.Bundle;
import android.widget.TextView;

/**
 * Dialog for confirming the enrollment for a certain identity / identity_provider.
 */
public class EnrollmentConfirmationActivity extends AbstractConfirmationActivity {

    private final static int EnrollmentChallengeResponseCodeSuccess = 1;
    private final static int EnrollmentChallengeResponseCodeVerificationRequired = 100;
    private final static int EnrollmentChallengeResponseCodeSuccessUsernameByServer = 101;
    private final static int EnrollmentChallengeResponseCodeFailureUsernameTaken = 102;
    private final static int EnrollmentChallengeResponseCodeFailure = 103;

    /**
     * Called when the activity is first created.
     */
    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setTitleText(R.string.enrollment_confirmation_title);
        setDescriptionText(R.string.enrollment_confirmation_description);
        setConfirmButtonText(R.string.enrollment_confirm_button);

        TextView enrollmentURLDomain = (TextView)findViewById(R.id.enrollment_url_domain);
        try {
            URL enrollmentURL = new URL(((EnrollmentChallenge)_getChallenge()).getEnrollmentURL());
            enrollmentURLDomain.setText(enrollmentURL.getHost());
        } catch (MalformedURLException e) {
            // Nothing
        }
    }

    /*
     * (non-Javadoc)
     * 
     * @see org.tiqr.authenticator.general.AbstractConfirmationActivity#_getLayoutResource()
     */
    @Override
    protected int _getLayoutResource() {
        return R.layout.confirmation_enroll;
    }

    /**
     * Enroll on confirmation.
     */
    @Override
    protected void _onDialogConfirm() {
        AbstractActivityGroup parent = (AbstractActivityGroup)getParent();
        Intent enrollmentPincodeIntent = new Intent().setClass(this, EnrollmentPincodeActivity.class);
        parent.startChildActivity("EnrollmentPincodeActivity", enrollmentPincodeIntent);

        // _newSessionKey();
    }

    /**
     * Handle session key available -> enroll.
     */
    @Override
    public void onSessionKeyAvailable(SecretKey sessionKey) {
        _enroll(sessionKey);
    }

    /**
     * Handle cancel.
     */
    @Override
    protected void _onDialogCancel() {
        EnrollmentActivityGroup group = (EnrollmentActivityGroup)getParent();
        group.goToRoot();
    }

    /**
     * Handle finish.
     */
    @Override
    protected void _onDialogDone(boolean successful, boolean doReturn, boolean doRetry) {
        if (doReturn && _getChallenge().getReturnURL() != null) {
            _returnToChallengeUrl(successful);
        } else {
            EnrollmentActivityGroup group = (EnrollmentActivityGroup)getParent();
            group.finish(); // back to the scanner
        }
    }

    /**
     * Store identity (and identity provider if needed).
     */
    private void _storeIdentityAndIdentityProvider(SecretKey secret, SecretKey sessionKey) throws UserException {
        DbAdapter db = new DbAdapter(this);
        if (_getChallenge().getIdentityProvider().isNew() && !db.insertIdentityProvider(_getChallenge().getIdentityProvider())) {
            throw new UserException(getString(R.string.error_enroll_failed_to_store_identity_provider));
        }

        if (!db.insertIdentityForIdentityProvider(_getChallenge().getIdentity(), _getChallenge().getIdentityProvider())) {
            throw new UserException(getString(R.string.error_enroll_failed_to_store_identity));
        }

        Secret secretStore = Secret.secretForIdentity(_getChallenge().getIdentity(), this);
        secretStore.setSecret(secret);
        try {
            secretStore.storeInKeyStore(sessionKey);
        } catch (SecurityFeaturesException e) {
            _showAlertWithMessage(getString(R.string.enrollment_failure_title), getString(R.string.error_device_incompatible_with_security_standards), false, false);

        }
    }

    private String _keyToHex(SecretKey secret) {
        byte[] buf = secret.getEncoded();
        StringBuffer strbuf = new StringBuffer(buf.length * 2);
        int i;

        for (i = 0; i < buf.length; i++) {
            if (((int)buf[i] & 0xff) < 0x10)
                strbuf.append("0");

            strbuf.append(Long.toString((int)buf[i] & 0xff, 16));
        }

        return strbuf.toString();
    }

    /**
     * Send enrollment request to server.
     */
    private void _sendEnrollmentRequest(SecretKey secret) throws UserException {
        try {
            EnrollmentChallenge challenge = (EnrollmentChallenge)_getChallenge();

            HttpPost httpPost = new HttpPost(challenge.getEnrollmentURL());

            List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(3);
            nameValuePairs.add(new BasicNameValuePair("secret", _keyToHex(secret)));
            nameValuePairs.add(new BasicNameValuePair("language", Locale.getDefault().getLanguage()));
            String notificationAddress = NotificationRegistration.getNotificationToken(this);
            if (notificationAddress != null) {
                nameValuePairs.add(new BasicNameValuePair("notificationType", "C2DM"));
                nameValuePairs.add(new BasicNameValuePair("notificationAddress", notificationAddress));
            }
            nameValuePairs.add(new BasicNameValuePair("operation", "register"));

            Config config = new Config(this);
            nameValuePairs.add(new BasicNameValuePair("version", config.getTIQRLoginProtocolVersion()));

            httpPost.setEntity(new UrlEncodedFormEntity(nameValuePairs, HTTP.UTF_8));
            if (_getChallenge().getIdentityProvider().getVersion() >= 1.0f) {
                httpPost.setHeader("ACCEPT", "application/json");
            }

            DefaultHttpClient httpClient = new DefaultHttpClient();
            HttpResponse httpResponse = httpClient.execute(httpPost);

            if (_getChallenge().getIdentityProvider().getVersion() >= 1.0f) {
                JSONObject response = new JSONObject(EntityUtils.toString(httpResponse.getEntity()));

                int responseCode = 0;
                try {
                    responseCode = response.getInt("responseCode");
                } catch (JSONException e) {
                    throw new UserException(getString(R.string.error_enroll_invalid_response));
                }

                if (responseCode != EnrollmentChallengeResponseCodeSuccess) {
                    try {
                        String message = response.getString("message");
                        throw new UserException(message);
                    } catch (JSONException e) {
                        // TODO add strings for other exception possibilitys
                        throw new UserException(getString(R.string.enrollment_failure_message));
                    }
                }
            } else {
                String response = EntityUtils.toString(httpResponse.getEntity());
                if (!response.equals("OK")) {
                    throw new UserException(response);
                }
            }
        } catch (UserException ex) {
            throw ex;
        } catch (Exception ex) {
            throw new UserException(getString(R.string.error_enroll_connect_error), ex);
        }
    }

    /**
     * Generate identity secret.
     * 
     * @return secret key
     * 
     * @throws UserException
     */
    private SecretKey _generateSecret() throws UserException {
        try {
            return Encryption.generateRandomSecretKey();
        } catch (Exception ex) {
            throw new UserException(getString(R.string.error_enroll_failed_to_generate_secret));
        }
    }

    /**
     * Enroll user.
     */
    private void _enroll(SecretKey sessionKey) {
        try {
            SecretKey secret = _generateSecret();
            _sendEnrollmentRequest(secret);
            _storeIdentityAndIdentityProvider(secret, sessionKey);
            Object[] args = new Object[] { _getChallenge().getIdentity().getDisplayName(), _getChallenge().getIdentityProvider().getDisplayName() };
            String message = getString(R.string.enrollment_success_message, args);
            _showAlertWithMessage(getString(R.string.enrollment_success_title), message, true, false);
        } catch (UserException ex) {
            _showAlertWithMessage(getString(R.string.enrollment_failure_title), ex.getMessage(), false, false);
        }
    }

}