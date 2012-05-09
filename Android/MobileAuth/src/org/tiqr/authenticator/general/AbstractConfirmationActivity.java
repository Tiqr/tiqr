package org.tiqr.authenticator.general;

import org.tiqr.authenticator.LoginDialog;
import org.tiqr.authenticator.R;
import org.tiqr.authenticator.auth.Challenge;
import org.tiqr.authenticator.protection.SessionKeyAvailabilityListener;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.DialogInterface;
import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.Window;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;

/**
 * Confirmation activity base class for authentication and enrollment confirmation.
 */
public abstract class AbstractConfirmationActivity extends Activity implements SessionKeyAvailabilityListener
{
    /**
     * Returns the challenge.
     * 
     * @return challenge
     */
    protected Challenge _getChallenge()
    {
        AbstractActivityGroup parent = (AbstractActivityGroup) getParent();
        return parent.getChallenge();
    }
    
    /**
     * Some activities require the session pin code. They should call this method which 
     * triggers pin retrieval. The pin becomes available through the onSessionKeyAvailable
     * methods, which is triggered immediately if the pin is still in memory, or after
     * the user has completed the pin dialog (which is why this is asynchronous)
     */
    protected void _requestSessionKey()
    {
        LoginDialog.requestSessionKey(getParent(), _getChallenge().getIdentity().getIdentifier(), this);
    }
    
    /**
     * Some activities require the session pin code. They should call this method which 
     * triggers pin retrieval. The pin becomes available through the onSessionKeyAvailable
     * methods, which is triggered immediately if the pin is still in memory, or after
     * the user has completed the pin dialog (which is why this is asynchronous)
     */
    protected void _newSessionKey()
    {
        LoginDialog.newSessionKey(getParent(), _getChallenge().getIdentity().getIdentifier(), this);
    }
    
    /**
     * Called when the user chooses OK in the dialog.
     */
    protected abstract void _onDialogConfirm();
    
    /**
     * Called when the user chooses Cancel in the dialog.
     */
    protected abstract void _onDialogCancel();
    
    /**
     * Called when the user has closed the Alert dialog.
     * 
     * @param successful successful operation?
     * @param doReturn   return to previous activity?
     * @param doRetry    try again?
     */
    protected abstract void _onDialogDone(boolean successful, boolean doReturn, boolean doRetry);
    
    /**
     * Called to determine the layout to use. Defaults to confirmation. Subclasses have to override it.
     */
    abstract protected int _getLayoutResource();
    
    /**
     *  Called when the activity is first created. 
     */
    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        
        requestWindowFeature(Window.FEATURE_PROGRESS);
        
        setContentView(_getLayoutResource());        
      
        TextView dn = (TextView)findViewById(R.id.display_name);
        dn.setText(_getChallenge().getIdentity().getDisplayName());
        
        TextView ipdn = (TextView)findViewById(R.id.identity_provider_name);
        ipdn.setText(_getChallenge().getIdentityProvider().getDisplayName());
        
        ImageView i = (ImageView)findViewById(R.id.identity_provider_logo);
        i.setImageBitmap(_getChallenge().getIdentityProvider().getLogoBitmap());
          
        final Button ok = (Button)findViewById(R.id.confirm_button);
        
        if (ok != null) {
            ok.setOnClickListener(new OnClickListener() {  
                public void onClick(View v) {  
                    setProgressBarVisibility(true);
                    
                    ok.setEnabled(false);
                    
                    _onDialogConfirm();
                }  
            }); 
        }
    }
    
    /**
     * Change the title.
     * 
     * @param resourceId resource 
     */
    public void setTitleText(int resourceId)
    {
       TextView view = (TextView)findViewById(R.id.title);
       view.setText(resourceId);
    } 
    
    /**
     * Change the description.
     * 
     * @param resourceId resource 
     */
    public void setDescriptionText(int resourceId)
    {
       TextView view = (TextView)findViewById(R.id.description);
       view.setText(resourceId);
    }      
    
    /**
     * Change the title of the OK button.
     * 
     * @param resourceId resource 
     */
    public void setConfirmButtonText(int resourceId)
    {
       Button ok = (Button)findViewById(R.id.confirm_button);
       ok.setText(resourceId);
    }
        
    /**
     * Returns to the challenge return URL.
     *
     * @param successful successful?
     */
    protected void _returnToChallengeUrl(boolean successful)
    {
        String url = _getChallenge().getReturnURL();
        
        if (url.indexOf("?")>=0) {
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
     * Show alert with the given message. This alert is shown after confirmation
     * by the user and the operation is successful or not. 
     * 
     * @param title      title
     * @param message    message
     * @param successful successful?
     * @param retry      allow retry on failure?
     */
    protected void _showAlertWithMessage(String title, String message, final boolean successful, boolean retry) 
    {
        setProgressBarVisibility(false);
       
        AlertDialog.Builder builder =
            new AlertDialog.Builder(getParent())
                .setTitle(title)
                .setMessage(message);
        
        if (retry) {
            builder.setPositiveButton(getString(R.string.retry_button), new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which)
                {
                    _onDialogDone(successful, false, true);
                }
            });
            
            builder.setNegativeButton(getString(R.string.cancel_button), new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which)
                {
                    _onDialogDone(successful, false, false);
                }
            });                       
        } else {
            builder.setPositiveButton(getString(R.string.ok_button), new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which)
                {
                    _onDialogDone(successful, false, false);
                }
            });
        }
            
        if (_getChallenge().getReturnURL() != null) {
            builder.setNeutralButton(getString(R.string.return_button), new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which)
                {
                    _onDialogDone(successful, true, false);
                }
            });      
        }        

        builder.show();
    }
}