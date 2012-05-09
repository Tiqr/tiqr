package org.tiqr.authenticator.identity;

import org.tiqr.authenticator.R;
import org.tiqr.authenticator.datamodel.DbAdapter;
import android.content.Context;
import android.database.Cursor;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.view.View;
import android.widget.ImageView;
import android.widget.SimpleCursorAdapter;
import android.widget.TextView;

public class IdentityCursorAdapter extends SimpleCursorAdapter
{

    public IdentityCursorAdapter(Context context, int layout, Cursor c,
            String[] from, int[] to)
    {
        super(context, layout, c, from, to);
    }

    public final void bindView(final View v, final Context context, final Cursor cursor)
    {
        ImageView i = (ImageView)v.findViewById(R.id.identity_provider_logo);
        
        byte[] logoData = cursor.getBlob(cursor.getColumnIndex(DbAdapter.LOGO));
        
        Bitmap logoBitmap = BitmapFactory.decodeByteArray(logoData, 0, logoData.length);
        
        i.setImageBitmap(logoBitmap);
        
        int blocked = cursor.getInt(cursor.getColumnIndex(DbAdapter.BLOCKED));
    	TextView blockedText = (TextView)v.findViewById(R.id.blocked);

        if (blocked == 0) {
        	blockedText.setVisibility(View.INVISIBLE);
        	blockedText.setEnabled(false);
        	blockedText.setHeight(0);
        }
        
        super.bindView(v, context, cursor);
    }
}
