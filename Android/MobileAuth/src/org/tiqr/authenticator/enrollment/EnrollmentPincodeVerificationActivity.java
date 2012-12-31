package org.tiqr.authenticator.enrollment;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Locale;
import java.util.Map;

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
import org.tiqr.authenticator.IncompatibilityDialog;
import org.tiqr.authenticator.NotificationRegistration;
import org.tiqr.authenticator.R;
import org.tiqr.authenticator.auth.Challenge;
import org.tiqr.authenticator.auth.EnrollmentChallenge;
import org.tiqr.authenticator.datamodel.DbAdapter;
import org.tiqr.authenticator.exceptions.SecurityFeaturesException;
import org.tiqr.authenticator.exceptions.UserException;
import org.tiqr.authenticator.general.AbstractActivityGroup;
import org.tiqr.authenticator.general.AbstractPincodeActivity;
import org.tiqr.authenticator.security.Encryption;
import org.tiqr.authenticator.security.Secret;

import android.app.AlertDialog;
import android.content.DialogInterface;
import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.os.Handler;
import android.os.Message;
import android.view.View;
import android.widget.TextView;

public class EnrollmentPincodeVerificationActivity extends AbstractPincodeActivity {

    protected String firstPin;

    private final static int EnrollmentChallengeResponseCodeSuccess = 1;
    private final static int EnrollmentChallengeResponseCodeVerificationRequired = 100;
    private final static int EnrollmentChallengeResponseCodeSuccessUsernameByServer = 101;
    private final static int EnrollmentChallengeResponseCodeFailureUsernameTaken = 102;
    private final static int EnrollmentChallengeResponseCodeFailure = 103;

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        firstPin = getIntent().getStringExtra("org.tiqr.firstPin");

        // Update the text.
        TextView intro = (TextView)findViewById(R.id.intro_label);
        intro.setText(R.string.login_verify_intro);
    }

    @Override
    public void process(View v) {
        String secondPin = pincode.getText().toString();
        if (!firstPin.equals(secondPin)) {
            _clear();
            // TODO Refactor _showErrorView to have 2 separate parameters instead of a HashMap
            Map<String, Object> details = new HashMap<String, Object>();
            details.put("title", getString(R.string.passwords_dont_match_title));
            details.put("message", getString(R.string.passwords_dont_match));
            _showErrorView(details);
            return;
        }

        _hideSoftKeyboard(pincode);
        try {
            _showProgressDialog(getString(R.string.enrolling));
            SecretKey sessionKey = Encryption.keyFromPassword(getParent(), secondPin);
            _enroll(sessionKey);
        } catch (SecurityFeaturesException e) {
            new IncompatibilityDialog().show(getParent());
        }
    }

    /**
     * Handle showing the alert view
     */
    private Handler _showAlertHandler = new Handler() {
        @Override
        public void handleMessage(Message msg) {
            progressDialog.cancel();
            _showAlertWithMessage(getString(R.string.enrollment_failure_title), ((UserException)msg.obj).getMessage(), false, false);
        }
    };

    /**
     * Handle showing the enrollment summary activity
     */
    private Handler _showEnrollmentSummaryHandler = new Handler() {
        @Override
        public void handleMessage(Message msg) {
            progressDialog.cancel();
            _showEnrollmentSummary((String)msg.obj);
        }
    };

    protected void _showEnrollmentSummary(String message) {
        EnrollmentActivityGroup group = (EnrollmentActivityGroup)getParent();
        Intent summaryIntent = new Intent(this, EnrollmentSummaryActivity.class);
        group.startChildActivity("EnrollmentSummaryActivity", summaryIntent);
    }

    /**
     * Enroll user
     * 
     * We run this in a new thread here because otherwise, the activity dialog wouldn't show
     * 
     * @param sessionKey
     */
    private void _enroll(final SecretKey sessionKey) {
        new Thread(new Runnable() {

            @Override
            public void run() {
                try {
                    SecretKey secret = _generateSecret();
                    _sendEnrollmentRequest(secret);
                    _storeIdentityAndIdentityProvider(secret, sessionKey);
                    Object[] args = new Object[] { _getChallenge().getIdentity().getDisplayName(), _getChallenge().getIdentityProvider().getDisplayName() };
                    String message = getString(R.string.enrollment_success_message, args);

                    Message msg = new Message();
                    msg.obj = message;
                    _showEnrollmentSummaryHandler.sendMessage(msg);
                } catch (UserException ex) {
                    Message msg = new Message();
                    msg.obj = ex;
                    _showAlertHandler.sendMessage(msg);
                }
            }

        }).start();
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
     * Returns the challenge.
     * 
     * @return challenge
     */
    protected Challenge _getChallenge() {
        AbstractActivityGroup parent = (AbstractActivityGroup)getParent();
        return parent.getChallenge();
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

            httpPost.setHeader("ACCEPT", "application/json");
            httpPost.setEntity(new UrlEncodedFormEntity(nameValuePairs, HTTP.UTF_8));

            DefaultHttpClient httpClient = new DefaultHttpClient();
            HttpResponse httpResponse = httpClient.execute(httpPost);
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
        } catch (UserException ex) {
            throw ex;
        } catch (Exception ex) {
            throw new UserException(getString(R.string.error_enroll_connect_error), ex);
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

    /**
     * Show alert with the given message. This alert is shown after confirmation by the user and the operation is successful or not.
     * 
     * @param title title
     * @param message message
     * @param successful successful?
     * @param retry allow retry on failure?
     */
    protected void _showAlertWithMessage(String title, String message, final boolean successful, boolean retry) {
        setProgressBarVisibility(false);

        AlertDialog.Builder builder = new AlertDialog.Builder(getParent()).setTitle(title).setMessage(message);

        if (retry) {
            builder.setPositiveButton(getString(R.string.retry_button), new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    _onDialogDone(successful, false, true);
                }
            });

            builder.setNegativeButton(getString(R.string.cancel_button), new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    _onDialogDone(successful, false, false);
                }
            });
        } else {
            builder.setPositiveButton(getString(R.string.ok_button), new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    _onDialogDone(successful, false, false);
                }
            });
        }

        if (_getChallenge().getReturnURL() != null) {
            builder.setNeutralButton(getString(R.string.return_button), new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    _onDialogDone(successful, true, false);
                }
            });
        }

        builder.show();
    }

    /**
     * Handle finish.
     */
    protected void _onDialogDone(boolean successful, boolean doReturn, boolean doRetry) {
        if (doReturn && _getChallenge().getReturnURL() != null) {
            _returnToChallengeUrl(successful);
        } else {
            EnrollmentActivityGroup group = (EnrollmentActivityGroup)getParent();
            group.finish(); // back to the scanner
        }
    }

    /**
     * Returns to the challenge return URL.
     * 
     * @param successful successful?
     */
    protected void _returnToChallengeUrl(boolean successful) {
        String url = _getChallenge().getReturnURL();

        if (url.indexOf("?") >= 0) {
            url = url + "&succesful=" + successful;
        } else {
            url = url + "?succesful=" + successful;
        }

        try {
            Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(url));
            startActivity(intent);
        } catch (Exception ex) {
            _onDialogCancel();
        }
    }

    /**
     * Handle cancel.
     */
    protected void _onDialogCancel() {
        EnrollmentActivityGroup group = (EnrollmentActivityGroup)getParent();
        group.goToRoot();
    }
}
