package org.tiqr.authenticator;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.DialogInterface;

public class IncompatibilityDialog
{
    public void show(final Activity activity)
    {
        AlertDialog.Builder dialog = new AlertDialog.Builder(activity);
        dialog.setMessage(activity.getString(R.string.error_device_incompatible_with_security_standards));
        dialog.setCancelable(false);
        dialog.setPositiveButton(activity.getString(R.string.ok_button), new DialogInterface.OnClickListener() {
        public void onClick(DialogInterface dialog, int id) {
            activity.finish();
        }
        });
        
        AlertDialog alert = dialog.create();
        alert.setTitle(R.string.error_title);
        // Icon for AlertDialog
        alert.show();
    }
}
