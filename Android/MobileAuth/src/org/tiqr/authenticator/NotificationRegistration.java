package org.tiqr.authenticator;

import java.util.ArrayList;
import java.util.List;

import org.apache.http.HttpResponse;
import org.apache.http.NameValuePair;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.message.BasicNameValuePair;
import org.apache.http.protocol.HTTP;
import org.apache.http.util.EntityUtils;

import android.content.Context;
import android.content.SharedPreferences;
import android.util.Log;

public class NotificationRegistration
{
    public static final String TOKENEXCHANGE_URL = "https://mobi.surfnet.nl/tokenexchange/?appId=tiqr";

    private static void _sendRequestWithDeviceToken(Context context, final String deviceToken) throws Exception
    {
        String notificationToken = getNotificationToken(context);

        HttpPost httpPost = new HttpPost(TOKENEXCHANGE_URL);
        List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>();
        nameValuePairs.add(new BasicNameValuePair("deviceToken", deviceToken));
        if (notificationToken != null) {
            nameValuePairs.add(new BasicNameValuePair("notificationToken", notificationToken));
        }

        httpPost.setEntity(new UrlEncodedFormEntity(nameValuePairs, HTTP.UTF_8));

        DefaultHttpClient httpClient = new DefaultHttpClient();
        HttpResponse httpResponse;
        httpResponse = httpClient.execute(httpPost);

        notificationToken = EntityUtils.toString(httpResponse.getEntity());
        Log.d(NotificationRegistration.class.getSimpleName(), "Notification token: " + notificationToken);

        SharedPreferences settings = Prefs.get(context);        
        SharedPreferences.Editor editor = settings.edit();
        editor.putString("sa_notificationToken", notificationToken);
        editor.commit();
    }
    
    public static String getNotificationToken(final Context context)
    {
        SharedPreferences settings = Prefs.get(context);
        return settings.getString("sa_notificationToken", null);
    }

    public static void sendRequestWithDeviceToken(final Context context, final String deviceToken)
    {
        new Thread(new Runnable() {
            public void run()
            {
                try {
                    _sendRequestWithDeviceToken(context, deviceToken);
                } catch (Exception ex) {
                    Log.e(NotificationRegistration.class.getSimpleName(), "Error retrieving device notification token", ex);
                }
            }
        }).start();
    }
}