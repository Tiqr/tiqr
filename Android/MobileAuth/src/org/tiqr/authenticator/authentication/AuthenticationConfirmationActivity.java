package org.tiqr.authenticator.authentication;

import java.io.IOException;
import java.security.InvalidKeyException;
import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

import javax.crypto.SecretKey;

import org.apache.http.HttpResponse;
import org.apache.http.NameValuePair;
import org.apache.http.client.ClientProtocolException;
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
import org.tiqr.authenticator.auth.AuthenticationChallenge;
import org.tiqr.authenticator.exceptions.InvalidChallengeException;
import org.tiqr.authenticator.exceptions.SecurityFeaturesException;
import org.tiqr.authenticator.general.AbstractActivityGroup;
import org.tiqr.authenticator.general.AbstractConfirmationActivity;
import org.tiqr.authenticator.security.OCRAWrapper;
import org.tiqr.authenticator.security.Secret;

import android.content.Intent;
import android.os.Bundle;
import android.widget.TextView;

/**
 * Confirmation dialog for authentication challenge.
 * 
 * TODO: use string resources for title and error messages
 */
public class AuthenticationConfirmationActivity extends AbstractConfirmationActivity {

    private static final int AuthenticationChallengeResponseCodeSuccess = 1;
    private static final int AuthenticationChallengeResponseCodeFailure = 200;
    private static final int AuthenticationChallengeResponseCodeInvalidUsernamePasswordPin = 201;
    private static final int AuthenticationChallengeResponseCodeExpired = 202;
    private static final int AuthenticationChallengeResponseCodeInvalidChallenge = 203;
    private static final int AuthenticationChallengeResponseCodeAccountBlocked = 204;
    private static final int AuthenticationChallengeResponseCodeInvalidRequest = 205;

    /** Called when the activity is first created. */
    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setTitleText(R.string.authentication_confirmation_title);
        setDescriptionText(R.string.authentication_confirmation_description);
        setConfirmButtonText(R.string.authentication_confirm_button);

        // TODO: When a service provider identifier is available, switch these 2 around.
        TextView spdn = (TextView)findViewById(R.id.service_provider_display_name);
        spdn.setText(((AuthenticationChallenge)_getChallenge()).getServiceProviderDisplayName());

        TextView spi = (TextView)findViewById(R.id.service_provider_identifier);
        spi.setText("");
    }

    /*
     * (non-Javadoc)
     * 
     * @see org.tiqr.authenticator.general.AbstractConfirmationActivity#_getLayoutResource()
     */
    @Override
    protected int _getLayoutResource() {
        return R.layout.confirmation_auth;
    }

    /**
     * Confirm login.
     */
    @Override
    protected void _onDialogConfirm() {
        AbstractActivityGroup parent = (AbstractActivityGroup)getParent();
        Intent authenticationPincodeIntent = new Intent().setClass(this, AuthenticationPincodeActivity.class);
        parent.startChildActivity("AuthenticationPincodeActivity", authenticationPincodeIntent);

        // try {
        // _login();
        // } catch (SecurityFeaturesException e) {
        // new IncompatibilityDialog().show(this);
        // }
    }

    /**
     * Cancel dialog.
     */
    @Override
    protected void _onDialogCancel() {
        AuthenticationActivityGroup group = (AuthenticationActivityGroup)getParent();
        group.goToRoot();
    }

    /**
     * Handle finish.
     */
    @Override
    protected void _onDialogDone(boolean successful, boolean doReturn, boolean doRetry) {
        if (doReturn && _getChallenge().getReturnURL() != null) {
            AuthenticationActivityGroup group = (AuthenticationActivityGroup)getParent();
            group.goToRoot();
            _returnToChallengeUrl(successful);
        } else if (doRetry) {
            _onDialogConfirm();
        } else {
            AuthenticationActivityGroup group = (AuthenticationActivityGroup)getParent();
            group.finish(); // back to the scanner
        }
    }

    /**
     * Calculate response for challenge.
     * 
     * @return OCRA challenge response
     * 
     * @throws InvalidKeyException
     * @throws SecurityFeaturesException
     * @throws InvalidChallengeException
     */
    private void _initChallengeResponse() throws InvalidKeyException, SecurityFeaturesException, InvalidChallengeException {
        _requestSessionKey();
    }

    /**
     * PArse authentication response from server. (string)
     * 
     * @param response authentication response
     */
    private void _parseResponse(String response) {
        if (response != null && response.equals("OK")) {
            String message = getString(R.string.authentication_success_message, _getChallenge().getIdentity().getDisplayName(), _getChallenge().getIdentityProvider().getDisplayName());
            _showAlertWithMessage(getString(R.string.authentication_success_title), message, true, false);
        } else {
            String message = getString(R.string.error_auth_unknown_error);
            boolean retry = false;
            if (response.equals("INVALID_CHALLENGE")) {
                message = getString(R.string.error_auth_invalid_challenge);
            } else if (response.equals("INVALID_REQUEST")) {
                message = getString(R.string.error_auth_invalid_request);
            } else if (response.equals("INVALID_RESPONSE")) {
                message = getString(R.string.error_auth_invalid_response);
                retry = true;
            } else if (response.equals("INVALID_USERID")) {
                message = getString(R.string.error_auth_invalid_userid);
            }
            _showAlertWithMessage(getString(R.string.authentication_failure_title), message, false, retry);
        }

    }

    /**
     * Parse authentication response from server.
     * 
     * @param response authentication response
     */
    private void _parseResponse(JSONObject response) {
        // Parse JSON response
        try {
            int responseCode = response.getInt("responseCode");
            if (responseCode == AuthenticationChallengeResponseCodeSuccess) {
                String message = getString(R.string.authentication_success_message, _getChallenge().getIdentity().getDisplayName(), _getChallenge().getIdentityProvider().getDisplayName());
                _showAlertWithMessage(getString(R.string.authentication_success_title), message, true, false);
            } else {
                boolean retry = false;
                String message = getString(R.string.error_auth_unknown_error);
                if (responseCode == AuthenticationChallengeResponseCodeInvalidChallenge) {
                    message = getString(R.string.error_auth_invalid_challenge);
                } else if (responseCode == AuthenticationChallengeResponseCodeInvalidRequest) {
                    message = getString(R.string.error_auth_invalid_request);
                } else if (responseCode == AuthenticationChallengeResponseCodeInvalidUsernamePasswordPin) {
                    message = getString(R.string.error_auth_invalid_userid);
                }

                _showAlertWithMessage(getString(R.string.authentication_failure_title), message, false, retry);
            }
        } catch (JSONException e) {
            _showAlertWithMessage(getString(R.string.authentication_failure_title), getString(R.string.error_auth_invalid_challenge), false, false);
        }
    }

    /**
     * Try to authenticate the user.
     * 
     * @throws SecurityFeaturesException
     */
    private void _login() throws SecurityFeaturesException {
        try {
            _initChallengeResponse();
        } catch (InvalidKeyException e) {
            _showAlertWithMessage(getString(R.string.authentication_failure_title), getString(R.string.error_auth_invalid_key), false, false);
        } catch (InvalidChallengeException e) {
            _showAlertWithMessage(getString(R.string.authentication_failure_title), getString(R.string.error_auth_invalid_challenge), false, false);
        }
    }

    private void _authenticateAtServer(String response) {
        AuthenticationChallenge challenge = (AuthenticationChallenge)_getChallenge();

        try {
            DefaultHttpClient httpclient = new DefaultHttpClient();
            HttpPost httppost = new HttpPost(challenge.getIdentityProvider().getAuthenticationURL());

            // Add your dNameValuePair
            List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(2);
            nameValuePairs.add(new BasicNameValuePair("sessionKey", challenge.getSessionKey()));
            nameValuePairs.add(new BasicNameValuePair("userId", challenge.getIdentity().getIdentifier()));
            nameValuePairs.add(new BasicNameValuePair("response", response));
            nameValuePairs.add(new BasicNameValuePair("language", Locale.getDefault().getLanguage()));
            String notificationAddress = NotificationRegistration.getNotificationToken(this);
            if (notificationAddress != null) {
                // communicate latest notification type and address
                nameValuePairs.add(new BasicNameValuePair("notificationType", "C2DM"));
                nameValuePairs.add(new BasicNameValuePair("notificationAddress", notificationAddress));
            }

            nameValuePairs.add(new BasicNameValuePair("operation", "login"));

            Config config = new Config(this);
            nameValuePairs.add(new BasicNameValuePair("version", config.getTIQRLoginProtocolVersion()));

            httppost.setEntity(new UrlEncodedFormEntity(nameValuePairs, HTTP.UTF_8));

            if (_getChallenge().getIdentityProvider().getVersion() >= 1.0f) {
                httppost.setHeader("ACCEPT", "application/json");
            }

            // Execute HTTP Post Request
            HttpResponse httpresponse = httpclient.execute(httppost);
            if (_getChallenge().getIdentityProvider().getVersion() > 1.0f) {
                try {
                    JSONObject serverResponse = new JSONObject(EntityUtils.toString(httpresponse.getEntity()));
                    _parseResponse(serverResponse);
                } catch (Exception e) {
                    _showAlertWithMessage(getString(R.string.authentication_failure_title), getString(R.string.error_auth_invalid_challenge), false, true);
                }
            } else {
                _parseResponse(EntityUtils.toString(httpresponse.getEntity()));
            }

        } catch (ClientProtocolException e) {
            _showFallbackOtp();
        } catch (IOException e) {
            _showFallbackOtp();
        }

    }

    protected void _showFallbackOtp() {
        // Clear current from the stack so back goes back one deeper.
        finish();
        AuthenticationActivityGroup group = (AuthenticationActivityGroup)getParent();
        Intent fallbackIntent = new Intent(this, AuthenticationFallbackActivity.class);
        group.startChildActivity("AuthenticationFallbackActivity", fallbackIntent);
    }

    @Override
    public void onSessionKeyAvailable(SecretKey sessionKey) {
        AuthenticationChallenge challenge = (AuthenticationChallenge)_getChallenge();

        try {
            Secret secret = Secret.secretForIdentity(challenge.getIdentity(), this);
            SecretKey secretKey = secret.getSecret(sessionKey);

            // working code: o = new Ocra("0123ABCDE456F".getBytes());
            // where 0123 would be the hex secret as it's on the serverside in
            // the json file.
            // will getSecret().getEncoded give us the same thing? Don't know
            // yet.
            String otp = OCRAWrapper.generateOCRA(challenge.getIdentityProvider().getOCRASuite(), secretKey.getEncoded(), challenge.getChallenge(), challenge.getSessionKey());

            _authenticateAtServer(otp);
        } catch (InvalidChallengeException e) {
            _showAlertWithMessage(getString(R.string.authentication_failure_title), getString(R.string.error_auth_invalid_challenge), false, false);
        } catch (ArrayIndexOutOfBoundsException e) {
            _showAlertWithMessage(getString(R.string.authentication_failure_title), getString(R.string.error_auth_server_incompatible), false, false);
        } catch (InvalidKeyException e) {
            _showAlertWithMessage(getString(R.string.authentication_failure_title), getString(R.string.error_auth_invalid_key), false, false);
        } catch (NumberFormatException e) {
            _showAlertWithMessage(getString(R.string.authentication_failure_title), getString(R.string.error_auth_invalid_challenge), false, false);
        } catch (SecurityFeaturesException e) {
            new IncompatibilityDialog().show(this);
        }
    }
}