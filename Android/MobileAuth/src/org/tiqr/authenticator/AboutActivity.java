package org.tiqr.authenticator;

import android.app.Activity;
import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;

/**
 * About screen.
 */
public class AboutActivity extends Activity
{
    /**
     * Open URL in browser.
     * 
     * @param url url
     */
    private void _openURL(String url)
    {
        Intent intent = new Intent();
        intent.setAction(Intent.ACTION_VIEW);
        intent.addCategory(Intent.CATEGORY_BROWSABLE);
        intent.setData(Uri.parse(url));
        startActivity(intent);
    }

    /**
     * Create activity.
     */
    @Override
    public void onCreate(Bundle savedInstanceState)
    {
        super.onCreate(savedInstanceState);

        setContentView(R.layout.about);

        ImageView logoSurfnet = (ImageView) findViewById(R.id.logo_surfnet);
        logoSurfnet.setOnClickListener(new View.OnClickListener() {
            public void onClick(View v)
            {
                _openURL("http://www.surfnet.nl/");
            }
        });

        ImageView logoEgeniq = (ImageView) findViewById(R.id.logo_egeniq);
        logoEgeniq.setOnClickListener(new View.OnClickListener() {
            public void onClick(View v)
            {
                _openURL("http://www.egeniq.com/");
            }
        });
        
        ImageView logoTiqr = (ImageView) findViewById(R.id.logo_tiqr);
        logoTiqr.setOnClickListener(new View.OnClickListener() {
            public void onClick(View v)
            {
                _openURL("http://tiqr.org/");
            }
        });
        
        ImageView logoStroomt = (ImageView) findViewById(R.id.logo_stroomt);
        logoStroomt.setOnClickListener(new View.OnClickListener() {
            public void onClick(View v)
            {
                _openURL("http://www.stroomt.com/");
            }
        });
    }
}
