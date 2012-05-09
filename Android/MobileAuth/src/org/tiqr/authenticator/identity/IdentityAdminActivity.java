package org.tiqr.authenticator.identity;

import org.tiqr.authenticator.R;
import org.tiqr.authenticator.datamodel.Identity;
import org.tiqr.authenticator.qr.CaptureActivity;

import android.content.Intent;
import android.database.Cursor;
import android.database.sqlite.SQLiteCursor;
import android.os.Bundle;
import android.view.ContextMenu;
import android.view.ContextMenu.ContextMenuInfo;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.view.View;
import android.widget.AdapterView.AdapterContextMenuInfo;
import android.widget.ListView;

public class IdentityAdminActivity extends AbstractIdentityListActivity
{
    @Override
    public void onCreate(Bundle savedInstanceState) 
    {
        super.onCreate(savedInstanceState);
        
       registerForContextMenu(getListView());

    }
    
    /* (non-Javadoc)
	 * @see android.app.ListActivity#onListItemClick(android.widget.ListView, android.view.View, int, long)
	 */
	@Override
	protected void onListItemClick(ListView l, View v, int position, long id) {
		super.onListItemClick(l, v, position, id);
		SQLiteCursor cursor = (SQLiteCursor)getListAdapter().getItem(position);
		Identity i = _db.createIdentityObjectForCurrentCursorPosition(cursor);
		_showIdentityDetail(i);
	}



	@Override 
    public boolean onCreateOptionsMenu(Menu menu) 
    {
        super.onCreateOptionsMenu(menu);
        MenuInflater inflater = getMenuInflater();
        inflater.inflate(R.menu.identity_menu, menu);        
        return true;
    }
    
    @Override
    public boolean onOptionsItemSelected(MenuItem item) 
    {
        switch (item.getItemId()) {
        case R.id.enroll:
            _enrollNewIdentity();
            return true;
        default:
            return super.onOptionsItemSelected(item);
        }
    }
    
    @Override
    public void onCreateContextMenu(ContextMenu menu, View v, ContextMenuInfo menuInfo) 
    {
        super.onCreateContextMenu(menu, v, menuInfo);
        
        AdapterContextMenuInfo info = (AdapterContextMenuInfo) menuInfo;
        
        // Set the popup menu header to the current display name
        Cursor c = getIdentityCursor();
        c.moveToPosition(info.position);       
        Identity identity = _db.createIdentityObjectForCurrentCursorPosition(c);
        menu.setHeaderTitle(identity.getDisplayName());
        
        MenuInflater inflater = getMenuInflater();
        inflater.inflate(R.menu.identity_context_menu, menu);
        
    }
    
    @Override
    public boolean onContextItemSelected(MenuItem item) 
    {
        AdapterContextMenuInfo info = (AdapterContextMenuInfo) item.getMenuInfo();
        switch (item.getItemId()) {
            case R.id.delete:
                _db.deleteIdentity(info.id);
                getIdentityCursor().requery();
                return true;
            default:
                return super.onContextItemSelected(item);
        }
    }
    
    /**
     * Show the detail activity for the identity
     * 
     * @param id	The identity
     */
    protected void _showIdentityDetail(Identity id)
    {
    	Intent intent = new Intent().setClass(this, IdentityDetailActivity.class);
    	intent.putExtra("org.tiqr.identity.id", id.getId());
        startActivity(intent);
    }
    
    /**
     * Start enrolling a new identity.
     */
    private void _enrollNewIdentity()
    {
        Intent intent = new Intent().setClass(this, CaptureActivity.class);
        startActivity(intent);
    }
}
