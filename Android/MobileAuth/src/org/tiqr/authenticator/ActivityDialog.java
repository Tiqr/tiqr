package org.tiqr.authenticator;

import android.app.Dialog;
import android.content.Context;
import android.view.ViewGroup.LayoutParams;
import android.widget.ProgressBar;

/**
 * Dialog used to show an activity indicator wheel.
 * 
 * We use this when enrolling and authenticating an identity
 * Internally, it uses a ProgressBar instance and a custom style
 */
public class ActivityDialog extends Dialog {

	public static ActivityDialog show(Context context) {
		return show(context, null, null, true);
	}

	public static ActivityDialog show(Context context, CharSequence title,
			CharSequence message, boolean indeterminate) {
		return show(context, title, message, indeterminate, false, null);
	}
	
	public static ActivityDialog show(Context context, CharSequence title,
			CharSequence message, boolean indeterminate, boolean cancelable,
			OnCancelListener cancelListener) {
		ActivityDialog dialog = new ActivityDialog(context);
		dialog.setTitle(title);
		dialog.setCancelable(cancelable);
		dialog.setOnCancelListener(cancelListener);

		/* The next line will add the ProgressBar to the dialog. */
		dialog.addContentView(new ProgressBar(context), new LayoutParams(
				LayoutParams.WRAP_CONTENT, LayoutParams.WRAP_CONTENT));
		dialog.show();

		return dialog;
	}

	public ActivityDialog(Context context) {
		super(context, R.style.ActivityDialog);
	}
}