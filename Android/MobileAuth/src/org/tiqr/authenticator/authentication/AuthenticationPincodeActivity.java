package org.tiqr.authenticator.authentication;

import java.io.IOException;
import java.security.InvalidKeyException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Locale;
import java.util.Map;

import javax.crypto.SecretKey;

import org.apache.http.Header;
import org.apache.http.HttpResponse;
import org.apache.http.NameValuePair;
import org.apache.http.ParseException;
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
import org.tiqr.authenticator.datamodel.DbAdapter;
import org.tiqr.authenticator.exceptions.InvalidChallengeException;
import org.tiqr.authenticator.exceptions.SecurityFeaturesException;
import org.tiqr.authenticator.general.AbstractActivityGroup;
import org.tiqr.authenticator.general.AbstractPincodeActivity;
import org.tiqr.authenticator.general.ErrorActivity;
import org.tiqr.authenticator.security.Encryption;
import org.tiqr.authenticator.security.OCRAProtocol;
import org.tiqr.authenticator.security.OCRAWrapper;
import org.tiqr.authenticator.security.OCRAWrapper_v1;
import org.tiqr.authenticator.security.Secret;

import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.net.Uri;
import android.os.Handler;
import android.os.Message;
import android.view.View;
import android.view.inputmethod.InputMethodManager;

public class AuthenticationPincodeActivity extends AbstractPincodeActivity {

    public static final int TIQRACRUnknownError = 101;
    public static final int TIQRACRConnectionError = 201;
    public static final int TIQRACRInvalidChallengeError = 301;
    public static final int TIQRACRInvalidRequestError = 302;
    public static final int TIQRACRInvalidResponseError = 303;
    public static final int TIQRACRInvalidUserError = 304;
    public static final int TIQRACRAccountBlockedError = 305;
    public static final int TIQRACRAccountBlockedErrorTemporary = 306;

    private static final int AuthenticationChallengeResponseCodeSuccess = 1;
    private static final int AuthenticationChallengeResponseCodeFailure = 200;
    private static final int AuthenticationChallengeResponseCodeInvalidUsernamePasswordPin = 201;
    private static final int AuthenticationChallengeResponseCodeInvalidChallenge = 203;
    private static final int AuthenticationChallengeResponseCodeAccountBlocked = 204;
    private static final int AuthenticationChallengeResponseCodeInvalidRequest = 202;
    private static final int AuthenticationChallengeResponseCodeInvalidUser = 205;

    /**
     * When the ok button has been pressed, user has entered the pin
     */
    @Override
    public void process(final View v) {
        _hideSoftKeyboard(pincode);
        _showProgressDialog(getString(R.string.authenticating));

        try {
            _login();
        } catch (SecurityFeaturesException e) {
            new IncompatibilityDialog().show(this);
        }
    }

    /**
     * Handle parsing the server response
     * 
     * This is a Handler, because the actual communication between the device and the server happens in a separate Thread. This way, using a Handler, we can
     * update the UI Thread.
     */
    private Handler _parseResponseHandler = new Handler() {
        @Override
        public void handleMessage(Message msg) {
            try {
                HttpResponse httpResponse = (HttpResponse)msg.obj;
                Header versionHeader = httpResponse.getFirstHeader("X-TIQR-Protocol-Version");
                if (versionHeader != null && versionHeader.getValue().equals("2")) {
                    _parseResponse(new JSONObject(EntityUtils.toString(httpResponse.getEntity())));
                } else {
                    _parseResponse(EntityUtils.toString(httpResponse.getEntity()));
                }
                progressDialog.cancel();

            } catch (JSONException e) {
                progressDialog.cancel();
                _showFallbackActivity();
            } catch (ParseException e) {
                progressDialog.cancel();
                _showFallbackActivity();
            } catch (IOException e) {
                progressDialog.cancel();
                _showFallbackActivity();
            }
        }
    };

    /**
     * Handle showing the fallback Activity
     */
    private Handler _showFallbackHandler = new Handler() {
        @Override
        public void handleMessage(Message msg) {
            progressDialog.cancel();
            _showFallbackActivity();
        }
    };

    /**
     * Try to authenticate the user.
     * 
     * @throws SecurityFeaturesException
     */
    private void _login() throws SecurityFeaturesException {
        String pin = pincode.getText().toString();

        try {
            SecretKey sessionKey = Encryption.keyFromPassword(getParent(), pin);
            AuthenticationChallenge challenge = (AuthenticationChallenge)_getChallenge();
            Secret secret = Secret.secretForIdentity(challenge.getIdentity(), this);
            SecretKey secretKey = secret.getSecret(sessionKey);

            // working code: o = new Ocra("0123ABCDE456F".getBytes());
            // where 0123 would be the hex secret as it's on the serverside in
            // the json file.
            // will getSecret().getEncoded give us the same thing? Don't know
            // yet.
            OCRAProtocol ocra;
            if (challenge.getProtocolVersion().equals("1")) {
                ocra = new OCRAWrapper_v1();
            } else {
                ocra = new OCRAWrapper();
            }
            String otp = ocra.generateOCRA(challenge.getIdentityProvider().getOCRASuite(), secretKey.getEncoded(), challenge.getChallenge(), challenge.getSessionKey());

            _authenticateAtServer(otp);
        } catch (InvalidChallengeException e) {
            _showErrorActivityWithMessage(getString(R.string.authentication_failure_title), getString(R.string.error_auth_invalid_challenge));
        } catch (ArrayIndexOutOfBoundsException e) {
            _showErrorActivityWithMessage(getString(R.string.authentication_failure_title), getString(R.string.error_auth_server_incompatible));
        } catch (InvalidKeyException e) {
            _showErrorActivityWithMessage(getString(R.string.authentication_failure_title), getString(R.string.error_auth_invalid_key));
        } catch (NumberFormatException e) {
            _showErrorActivityWithMessage(getString(R.string.authentication_failure_title), getString(R.string.error_auth_invalid_challenge));
        } catch (SecurityFeaturesException e) {
            new IncompatibilityDialog().show(this);
        }
    }

    private void _authenticateAtServer(String response) {
        AuthenticationChallenge challenge = (AuthenticationChallenge)_getChallenge();

        try {
            final DefaultHttpClient httpclient = new DefaultHttpClient();
            final HttpPost httppost = new HttpPost(challenge.getIdentityProvider().getAuthenticationURL());

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

            httppost.addHeader("ACCEPT", "application/json");
            httppost.addHeader("X-TIQR-Protocol-Version", config.getTIQRProtocolVersion());

            httppost.setEntity(new UrlEncodedFormEntity(nameValuePairs, HTTP.UTF_8));

            // Execute HTTP Post Request
            // We use a new thread here because otherwise, the activity dialog wouldn't show
            new Thread(new Runnable() {

                @Override
                public void run() {
                    HttpResponse httpresponse = null;
                    try {
                        httpresponse = httpclient.execute(httppost);
                        Message msg = new Message();
                        msg.obj = httpresponse;
                        _parseResponseHandler.sendMessage(msg);
                    } catch (ClientProtocolException e) {
                        _showFallbackHandler.sendEmptyMessage(0);
                    } catch (IOException e) {
                        _showFallbackHandler.sendEmptyMessage(0);
                    }
                }

            }).start();

        } catch (IOException e) {
            _showFallbackActivity();
        }
    }

    protected void _showFallbackActivity() {
        // Clear current from the stack so back goes back one deeper.
        progressDialog.cancel();
        finish();
        AuthenticationActivityGroup group = (AuthenticationActivityGroup)getParent();
        Intent fallbackIntent = new Intent(this, AuthenticationFallbackActivity.class);
        fallbackIntent.putExtra("org.tiqr.authentication.pincode", pincode.getText().toString());
        group.startChildActivity("AuthenticationFallbackActivity", fallbackIntent);
    }

    protected void _showAuthenticationSummary(String message) {
        AuthenticationActivityGroup group = (AuthenticationActivityGroup)getParent();
        Intent summaryIntent = new Intent(this, AuthenticationSummaryActivity.class);
        group.startChildActivity("AuthenticationSummaryActivity", summaryIntent);
    }

    /**
     * Parse authentication response form server. (plain string)
     * 
     * @param response authentication response
     */
    private void _parseResponse(String response) {
        InputMethodManager imm = (InputMethodManager)getSystemService(Context.INPUT_METHOD_SERVICE);
        imm.hideSoftInputFromWindow(pincode.getWindowToken(), 0);

        int code = TIQRACRUnknownError;
        String title = getString(R.string.unknown_error);
        String message = getString(R.string.error_auth_unknown_error);
        int attemptsLeft = -1;

        if (response != null && response.equals("OK")) {
            message = getString(R.string.authentication_success_message, _getChallenge().getIdentity().getDisplayName(), _getChallenge().getIdentityProvider().getDisplayName());
            _showAuthenticationSummary(message);
        } else {
            if (response.equals("ACCOUNT_BLOCKED")) {
                code = TIQRACRAccountBlockedError;
                title = getString(R.string.error_auth_account_blocked_title);
                message = getString(R.string.error_auth_account_blocked_message);
            } else if (response.equals("INVALID_CHALLENGE")) {
                code = TIQRACRInvalidChallengeError;
                title = getString(R.string.error_auth_invalid_challenge_title);
                message = getString(R.string.error_auth_invalid_challenge_message);
            } else if (response.equals("INVALID_REQUEST")) {
                code = TIQRACRInvalidRequestError;
                title = getString(R.string.error_auth_invalid_request_title);
                message = getString(R.string.error_auth_invalid_request_message);
            } else if (response.equals("INVALID_RESPONSE")) {
            	code = TIQRACRInvalidResponseError;
            	message = getString(R.string.error_auth_invalid_response);
            } else if (response.substring(0, 17).equals("INVALID_RESPONSE:")) {
                attemptsLeft = Integer.parseInt(response.substring(17, 18));
                code = TIQRACRInvalidResponseError;
                if (attemptsLeft > 1) {
                    title = getString(R.string.error_auth_wrong_pin);
                    message = String.format(getString(R.string.error_auth_x_attempts_left), attemptsLeft);
                } else if (attemptsLeft == 1) {
                    title = getString(R.string.error_auth_wrong_pin);
                    message = getString(R.string.error_auth_one_attempt_left);
                } else {
                    title = getString(R.string.error_auth_account_blocked_title);
                    message = getString(R.string.error_auth_account_blocked_message);
                }
            } else if (response.equals("INVALID_USERID")) {
                code = TIQRACRInvalidUserError;
                title = getString(R.string.error_auth_invalid_account);
                message = getString(R.string.error_auth_invalid_account_message);
            }

            Map<String, Object> details = new HashMap<String, Object>();
            details.put("title", title);
            details.put("message", message);
            if (attemptsLeft != -1) {
                details.put("attemptsLeft", attemptsLeft);
            }

            _authenticationFailed(code, details);
        }
    }

    /**
     * Parse authentication response from server. (json)
     * 
     * @param response authentication response
     */
    private void _parseResponse(JSONObject response) {
        InputMethodManager imm = (InputMethodManager)getSystemService(Context.INPUT_METHOD_SERVICE);
        imm.hideSoftInputFromWindow(pincode.getWindowToken(), 0);

        int code = TIQRACRUnknownError;
        String title = getString(R.string.unknown_error);
        String message = getString(R.string.error_auth_unknown_error);
        int attemptsLeft = -1;

        try {
            int responseCode = response.getInt("responseCode");
            if (responseCode == AuthenticationChallengeResponseCodeSuccess) {
                message = getString(R.string.authentication_success_message, _getChallenge().getIdentity().getDisplayName(), _getChallenge().getIdentityProvider().getDisplayName());
                _showAuthenticationSummary(message);
            } else { 
                if (responseCode == AuthenticationChallengeResponseCodeAccountBlocked) {
                    if (response.has("duration")) {
                        int duration = response.getInt("duration");
                        code = TIQRACRAccountBlockedErrorTemporary;
                        title = getString(R.string.error_auth_account_blocked_temporary_title);
                        message = String.format(getString(R.string.error_auth_account_blocked_temporary_message), duration);
                    } else {
                        code = TIQRACRAccountBlockedError;
                        title = getString(R.string.error_auth_account_blocked_title);
                        message = getString(R.string.error_auth_account_blocked_message);
                    }
                } else if (responseCode == AuthenticationChallengeResponseCodeInvalidChallenge) {
                    code = TIQRACRInvalidChallengeError;
                    title = getString(R.string.error_auth_invalid_challenge_title);
                    message = getString(R.string.error_auth_invalid_challenge_message);
                } else if (responseCode == AuthenticationChallengeResponseCodeInvalidRequest) {
                    code = TIQRACRInvalidRequestError;
                    title = getString(R.string.error_auth_invalid_request_title);
                    message = getString(R.string.error_auth_invalid_request_message);
                } else if (responseCode == AuthenticationChallengeResponseCodeInvalidUsernamePasswordPin) {
                    attemptsLeft = response.getInt("attemptsLeft");
                    code = TIQRACRInvalidResponseError;
                    if (attemptsLeft > 1) {
                        title = getString(R.string.error_auth_wrong_pin);
                        message = String.format(getString(R.string.error_auth_x_attempts_left), attemptsLeft);
                    } else if (attemptsLeft == 1) {
                        title = getString(R.string.error_auth_wrong_pin);
                        message = getString(R.string.error_auth_one_attempt_left);
                    } else {
                        title = getString(R.string.error_auth_account_blocked_title);
                        message = getString(R.string.error_auth_account_blocked_message);
                    }
                } else if (responseCode == AuthenticationChallengeResponseCodeInvalidUser) {
                    code = TIQRACRInvalidUserError;
                    title = getString(R.string.error_auth_invalid_account);
                    message = getString(R.string.error_auth_invalid_account_message); 
                }
    
                Map<String, Object> details = new HashMap<String, Object>();
                details.put("title", title);
                details.put("message", message);
                if (attemptsLeft != -1) {
                    details.put("attemptsLeft", attemptsLeft);
                }
                _authenticationFailed(code, details);
            }
        } catch (JSONException e) {
            Map<String, Object> details = new HashMap<String, Object>();
            details.put("title", title);
            details.put("message", message);
            _authenticationFailed(code, details);
        }

    }

    /**
     * What to do when PIN authentication fails
     * 
     * @param code The error code
     * @param details Error title and message, and optionally the number of login attempts
     */
    protected void _authenticationFailed(int code, Map<String, Object> details) {

        DbAdapter db = new DbAdapter(this);

        switch (code) {
            case TIQRACRConnectionError:
                _showFallbackActivity();
                break;
                
            case TIQRACRAccountBlockedError:
                _getChallenge().getIdentity().setBlocked(true);
                db.updateIdentity(_getChallenge().getIdentity());
                _showErrorActivity(details);
                break;

            case TIQRACRInvalidResponseError:
            	if (details.containsKey("attemptsLeft")) { 
            		int attemptsLeft = ((Integer)details.get("attemptsLeft")).intValue();
            		if (attemptsLeft == 0) {
            			db.blockAllIdentities();
            			_showErrorActivity(details);
            		} else {
            			_clear();
            			_showErrorView(details);
            			_initHiddenPincodeField();
            		}
                } else {
        			_clear();
        			_showErrorView(details);
        			_initHiddenPincodeField();
        		}
                break;
            default:
                _showErrorActivity(details);
        }
    }

    /**
     * Show the error activity and feed it with the error details
     * 
     * @param details The error title, message and optionally the number of login attempts
     */
    protected void _showErrorActivity(Map<String, Object> details) {
        AbstractActivityGroup parent = (AbstractActivityGroup)getParent();
        Intent intent = new Intent().setClass(this, ErrorActivity.class);

        intent.putExtra("org.tiqr.error.title", (String)details.get("title"));
        intent.putExtra("org.tiqr.error.message", (String)details.get("message"));
        if (details.containsKey("attemptsLeft")) {
            intent.putExtra("org.tiqr.error.attemptsLeft", Integer.parseInt(details.get("attemptsLeft").toString()));
        }

        parent.startChildActivity("ErrorActivity", intent);
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
     * Confirm login.
     */
    protected void _onDialogConfirm() {
        AbstractActivityGroup parent = (AbstractActivityGroup)getParent();
        Intent authenticationPincodeIntent = new Intent().setClass(this, AuthenticationPincodeActivity.class);
        parent.startChildActivity("AuthenticationPincodeActivity", authenticationPincodeIntent);
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
     * Cancel dialog.
     */
    protected void _onDialogCancel() {
        AuthenticationActivityGroup group = (AuthenticationActivityGroup)getParent();
        group.goToRoot();
    }
}
