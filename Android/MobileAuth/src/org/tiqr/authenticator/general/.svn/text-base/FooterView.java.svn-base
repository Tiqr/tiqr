package org.tiqr.authenticator.general;

import org.tiqr.authenticator.AboutActivity;
import org.tiqr.authenticator.R;

import android.content.Context;
import android.content.Intent;
import android.net.Uri;
import android.util.AttributeSet;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;

public class FooterView extends LinearLayout {
	public FooterView(Context context, AttributeSet attrs) {
		super(context, attrs);

		LayoutInflater layoutInflater = (LayoutInflater) context
				.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
		layoutInflater.inflate(R.layout.view_footer, this);
		
		ImageView infoImage = (ImageView)findViewById(R.id.footer_icon_info);
		infoImage.setOnClickListener(new OnClickListener() {
			@Override
			public void onClick(View v) {
				_showAboutActivity();
			}
		});
		
		OnClickListener ocl = new OnClickListener() {
			@Override
			public void onClick(View v) {
				switch (v.getId()) {
				case R.id.footer_tiqr_logo:
				case R.id.footer_tiqr_url:
					_openWebURL("http://www.tiqr.org");
					break;
				case R.id.footer_surfnet_logo:
					_openWebURL("http://www.surfnet.nl");
					break;
				}
			}
		};
		
		ImageView tiqrImage = (ImageView)findViewById(R.id.footer_tiqr_logo);
		ImageView surfnetImage = (ImageView)findViewById(R.id.footer_surfnet_logo);
		TextView tiqrUrl = (TextView)findViewById(R.id.footer_tiqr_url);
		
		tiqrImage.setOnClickListener(ocl);
		surfnetImage.setOnClickListener(ocl);
		tiqrUrl.setOnClickListener(ocl);
	}
	
	/**
	 * Start the About activity view
	 * 
	 */
	protected void _showAboutActivity() {
		Intent intent = new Intent(getContext().getApplicationContext(), AboutActivity.class);
		intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
		getContext().getApplicationContext().startActivity(intent);
	}
	
	/**
	 * When a user clicks one of the icons, open a browser
	 * 
	 * @param inURL
	 */
	protected void _openWebURL(String inURL) {
	    Intent browse = new Intent(Intent.ACTION_VIEW, Uri.parse(inURL));
	    browse.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
	    getContext().getApplicationContext().startActivity(browse);
	}
}