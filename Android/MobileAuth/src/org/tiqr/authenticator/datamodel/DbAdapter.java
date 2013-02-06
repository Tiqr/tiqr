package org.tiqr.authenticator.datamodel;

import java.util.ArrayList;

import android.content.ContentValues;
import android.content.Context;
import android.database.Cursor;
import android.database.SQLException;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;
import android.database.sqlite.SQLiteQueryBuilder;
import android.util.Log;

public class DbAdapter {
    public static final String ROWID = "_id";
    public static final String DISPLAY_NAME = "displayName";
    public static final String BLOCKED = "blocked";
    public static final String IDENTIFIER = "identifier";
    public static final String IDENTITYPROVIDER = "identityProvider";
    public static final String SORT_INDEX = "sortIndex";
    public static final String LOGO = "logo";
    public static final String INFO_URL = "infoUrl";
    public static final String AUTHENTICATION_URL = "authenticationUrl";
    public static final String OCRA_SUITE = "ocraSuite";
    public static final String VERSION = "version";

    private static final String DATABASE_NAME = "identities.db";
    private static final String TABLE_IDENTITY = "identity";
    private static final String TABLE_IDENTITYPROVIDER = "identityprovider";

    private static final String JOIN_IDENTITY_IDENTITYPROVIDER = TABLE_IDENTITY + " JOIN " + TABLE_IDENTITYPROVIDER + " ON " + TABLE_IDENTITY + "." + IDENTITYPROVIDER + " = " + TABLE_IDENTITYPROVIDER + "." + ROWID;

    private static final int DATABASE_VERSION = 5;

    private final Context _ctx;

    private DatabaseHelper _DBHelper;
    private SQLiteDatabase _db;

    public DbAdapter(Context context) {
        _ctx = context;
        _DBHelper = new DatabaseHelper(_ctx);

        _db = _DBHelper.getWritableDatabase();
    }

    private static class DatabaseHelper extends SQLiteOpenHelper {
        DatabaseHelper(Context context) {
            super(context, DATABASE_NAME, null, DATABASE_VERSION);
        }

        @Override
        public void onCreate(SQLiteDatabase db) {
            db.execSQL("CREATE TABLE " + TABLE_IDENTITYPROVIDER + " (" + ROWID + " INTEGER PRIMARY KEY AUTOINCREMENT, " + DISPLAY_NAME + " TEXT NOT NULL, " + IDENTIFIER + " TEXT NOT NULL, " + AUTHENTICATION_URL + " TEXT NOT NULL, "
                    + OCRA_SUITE + " TEXT NOT NULL, " + INFO_URL + " TEXT NOT NULL, " + LOGO + " BINARY, " + VERSION + " FLOAT);");

            db.execSQL("CREATE TABLE " + TABLE_IDENTITY + " (" + ROWID + " INTEGER PRIMARY KEY AUTOINCREMENT, " + BLOCKED + " INTEGER NOT NULL DEFAULT 0, " + DISPLAY_NAME + " TEXT NOT NULL, " + IDENTIFIER + " TEXT NOT NULL, "
                    + IDENTITYPROVIDER + " INTEGER NOT NULL, " + SORT_INDEX + " INTEGER NOT NULL);");

        }

        @Override
        public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {
            Log.w("DbAdapter", "Upgrading database from version " + oldVersion + " to " + newVersion + ", which will destroy all old data");
            db.execSQL("DROP TABLE IF EXISTS " + TABLE_IDENTITY);
            db.execSQL("DROP TABLE IF EXISTS " + TABLE_IDENTITYPROVIDER);
            onCreate(db);
        }
    }

    /**
     * Inserts an identity into the database.
     * 
     * The identity object's id is automatically set based on the new database row id.
     * 
     * @param Identity identity
     * @param IdentityProvider ip
     * 
     * @return insertion successful?
     */
    public boolean insertIdentityForIdentityProvider(Identity identity, IdentityProvider ip) {
        ContentValues values = new ContentValues();
        values.put(IDENTIFIER, identity.getIdentifier());
        values.put(DISPLAY_NAME, identity.getDisplayName());
        values.put(BLOCKED, identity.isBlocked() ? 1 : 0);
        values.put(IDENTITYPROVIDER, ip.getId());
        values.put(SORT_INDEX, identity.getSortIndex());

        long id = _db.insert(TABLE_IDENTITY, null, values);
        if (id != -1) {
            identity.setId(id);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Updates an existing identity.
     * 
     * @param identity identity
     * 
     * @return update successful?
     */
    public boolean updateIdentity(Identity identity) {
        ContentValues values = new ContentValues();
        values.put(IDENTIFIER, identity.getIdentifier());
        values.put(DISPLAY_NAME, identity.getDisplayName());
        values.put(BLOCKED, identity.isBlocked() ? 1 : 0);
        values.put(SORT_INDEX, identity.getSortIndex());

        return _db.update(TABLE_IDENTITY, values, ROWID + " = ?", new String[] { String.valueOf(identity.getId()) }) > 0;
    }

    /**
     * Deletes a particular identity.
     * 
     * @param identityId identity row-id
     * 
     * @return delete successful?
     */
    public boolean deleteIdentity(long identityId) {
        return _db.delete(TABLE_IDENTITY, ROWID + " = ?", new String[] { String.valueOf(identityId) }) > 0;
    }

    /**
     * Convenience method to block all available identities
     * 
     * @return The number of affected rows
     */
    public int blockAllIdentities() {
        ContentValues values = new ContentValues();
        values.put(BLOCKED, true);
        return _db.update(TABLE_IDENTITY, values, null, null);
    }

    public Identity createIdentityObjectForCurrentCursorPosition(Cursor cursor) {
        Identity identity = new Identity();

        int rowIdColumn = cursor.getColumnIndex(DbAdapter.ROWID);
        int identifierColumn = cursor.getColumnIndex(DbAdapter.IDENTIFIER);
        int displayNameColumn = cursor.getColumnIndex(DbAdapter.DISPLAY_NAME);
        int blockedColumn = cursor.getColumnIndex(DbAdapter.BLOCKED);
        int sortIndexColumn = cursor.getColumnIndex(DbAdapter.SORT_INDEX);

        identity.setId(cursor.getLong(rowIdColumn));
        identity.setIdentifier(cursor.getString(identifierColumn));
        identity.setDisplayName(cursor.getString(displayNameColumn));
        identity.setBlocked(cursor.getInt(blockedColumn) == 1 ? true : false);
        identity.setSortIndex(cursor.getInt(sortIndexColumn));

        return identity;
    }

    /**
     * Create identity objects for the results of the given cursor.
     * 
     * NOTE: this method closes the cursor when it's done!
     * 
     * @param cursor database cursor
     * 
     * @return Identity objects
     */
    private Identity[] _createIdentityObjectsForCursor(Cursor cursor) {
        ArrayList<Identity> identities = new ArrayList<Identity>();

        if (cursor.moveToFirst()) {

            do {
                Identity identity = createIdentityObjectForCurrentCursorPosition(cursor);
                identities.add(identity);
            } while (cursor.moveToNext());
        }

        cursor.close();

        Identity[] result = new Identity[identities.size()];
        return identities.toArray(result);
    }

    /**
     * Returns the identity with the given identifier and identity provider.
     * 
     * @param identifier identity identifier
     * @param identityProviderId identityprovider row-id
     * 
     * @return cursor object
     */
    public Cursor getIdentityByIdentifierAndIdentityProviderId(String identifier, long identityProviderId) throws SQLException {
        Cursor cursor = _db.query(TABLE_IDENTITY, new String[] { ROWID, DISPLAY_NAME, BLOCKED, IDENTIFIER, IDENTITYPROVIDER, SORT_INDEX }, IDENTIFIER + " = ? AND " + IDENTITYPROVIDER + " = ?",
                new String[] { identifier, String.valueOf(identityProviderId) }, null, null, null);

        if (cursor != null) {
            cursor.moveToFirst();
        }

        return cursor;
    }

    public Identity getIdentityByIdentityId(long identity_id) {
        SQLiteQueryBuilder builder = new SQLiteQueryBuilder();
        builder.setTables(JOIN_IDENTITY_IDENTITYPROVIDER);

        Cursor cursor = builder.query(_db, new String[] { TABLE_IDENTITY + "." + ROWID, TABLE_IDENTITY + "." + DISPLAY_NAME, TABLE_IDENTITY + "." + BLOCKED, TABLE_IDENTITY + "." + IDENTIFIER, TABLE_IDENTITY + "." + SORT_INDEX,
                TABLE_IDENTITYPROVIDER + "." + LOGO, }, TABLE_IDENTITY + "." + ROWID + " = ?", new String[] { String.valueOf(identity_id) }, null, null, SORT_INDEX);

        Identity[] identities = _createIdentityObjectsForCursor(cursor);
        if (identities.length > 0) {
            return identities[0];
        }

        return null;
    }

    /**
     * Returns the identity with the given identifier and identity provider identifier as an identity provider object.
     * 
     * @param identifier identity identifier
     * @param identityProviderId identity provider row-id
     * 
     * @return identity provider object or null if identity provider is unknown
     */
    public Identity getIdentityByIdentifierAndIdentityProviderIdAsObject(String identifier, long identityProviderId) {
        Identity[] identities = _createIdentityObjectsForCursor(getIdentityByIdentifierAndIdentityProviderId(identifier, identityProviderId));
        return identities.length == 1 ? identities[0] : null;
    }

    /**
     * Returns a cursor for all identities available in the system. The identities are ordered by their sort index.
     * 
     * @return cursor object
     */
    public Cursor getAllIdentities() {
        Cursor cursor = _db.query(TABLE_IDENTITY, new String[] { ROWID, DISPLAY_NAME, BLOCKED, IDENTIFIER, IDENTITYPROVIDER, SORT_INDEX }, null, null, null, null, SORT_INDEX);

        return cursor;
    }

    /**
     * Count how many identities there are in the database./
     * 
     * @return cursor object
     */
    public int identityCount() {

        Cursor cursor = getAllIdentities();
        int result = cursor.getCount();
        cursor.close();
        return result;
    }

    public Cursor getAllIdentitiesWithIdentityProviderData() {
        SQLiteQueryBuilder builder = new SQLiteQueryBuilder();
        builder.setTables(JOIN_IDENTITY_IDENTITYPROVIDER);

        Cursor cursor = builder.query(_db, new String[] { TABLE_IDENTITY + "." + ROWID, TABLE_IDENTITY + "." + DISPLAY_NAME, TABLE_IDENTITY + "." + BLOCKED, TABLE_IDENTITY + "." + IDENTIFIER, TABLE_IDENTITY + "." + SORT_INDEX,
                TABLE_IDENTITYPROVIDER + "." + LOGO, }, null, null, null, null, SORT_INDEX);

        return cursor;

    }

    /**
     * Returns the identities for the given identity provider ordered by their sort index.
     * 
     * Filter out the identities which are blocked, because this is only used for authentication
     * 
     * @param identityProviderId Identity provider row-id
     * 
     * @return result cursor
     */
    public Cursor findIdentitiesByIdentityProviderIdWithIdentityProviderData(long identityProviderId) throws SQLException {
        SQLiteQueryBuilder builder = new SQLiteQueryBuilder();
        builder.setTables(JOIN_IDENTITY_IDENTITYPROVIDER);

        Cursor cursor = builder.query(_db, new String[] { TABLE_IDENTITY + "." + ROWID, TABLE_IDENTITY + "." + DISPLAY_NAME, TABLE_IDENTITY + "." + BLOCKED, TABLE_IDENTITY + "." + IDENTIFIER, TABLE_IDENTITY + "." + SORT_INDEX,
                TABLE_IDENTITYPROVIDER + "." + LOGO, }, IDENTITYPROVIDER + " = ? AND " + TABLE_IDENTITY + "." + BLOCKED + " <> 1", new String[] { String.valueOf(identityProviderId) }, null, null, SORT_INDEX);

        if (cursor != null) {
            cursor.moveToFirst();
        }

        return cursor;
    }

    /**
     * Returns the identities for the given identity providers ordered by their sort index and returns them as an array of Identity objects.
     * 
     * @return result array
     */
    public Identity[] getAllIdentitiesAsObjects() {
        try {
            return _createIdentityObjectsForCursor(getAllIdentities());
        } catch (Exception ex) {
            return new Identity[0];
        }
    }

    /**
     * Returns the identities for the given identity provider ordered by their sort index.
     * 
     * @param identityProviderId identity provider row-id
     * 
     * @return result cursor
     */
    public Cursor findIdentitiesByIdentityProviderId(long identityProviderId) throws SQLException {
        Cursor cursor = _db.query(TABLE_IDENTITY, new String[] { ROWID, DISPLAY_NAME, BLOCKED, IDENTIFIER, IDENTITYPROVIDER, SORT_INDEX }, IDENTITYPROVIDER + " = ?", new String[] { String.valueOf(identityProviderId) }, null, null,
                SORT_INDEX);

        if (cursor != null) {
            cursor.moveToFirst();
        }

        return cursor;
    }

    /**
     * Returns the identities for the given identity provider ordered by their sort index and returns them as an array of Identity objects.
     * 
     * @param identityProviderId identity provider identifier
     * 
     * @return result array
     */
    public Identity[] findIdentitiesByIdentityProviderIdAsObjects(long identityProviderId) {
        try {
            return _createIdentityObjectsForCursor(findIdentitiesByIdentityProviderId(identityProviderId));
        } catch (Exception ex) {
            ex.printStackTrace();
            return new Identity[0];
        }
    }

    /**
     * Inserts an identity provider into the database.
     * 
     * TODO: logo
     * 
     * The identity provider object's id is automatically set based on the new database row id.
     * 
     * @param identityProvider The identity provider
     * 
     * @return insertion successful?
     */
    public boolean insertIdentityProvider(IdentityProvider identityProvider) {
        ContentValues values = new ContentValues();
        values.put(IDENTIFIER, identityProvider.getIdentifier());
        values.put(DISPLAY_NAME, identityProvider.getDisplayName());
        values.put(AUTHENTICATION_URL, identityProvider.getAuthenticationURL());
        values.put(OCRA_SUITE, identityProvider.getOCRASuite());
        values.put(LOGO, identityProvider.getLogoData());
        values.put(INFO_URL, identityProvider.getInfoURL());
        values.put(VERSION, identityProvider.getVersion());

        long id = _db.insert(TABLE_IDENTITYPROVIDER, null, values);
        if (id != -1) {
            identityProvider.setId(id);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Updates an existing identity provider.
     * 
     * @param identityProvider The identity provider
     * 
     * @return update successful?
     */
    public boolean updateIdentityProvider(IdentityProvider identityProvider) {
        ContentValues values = new ContentValues();
        values.put(IDENTIFIER, identityProvider.getIdentifier());
        values.put(DISPLAY_NAME, identityProvider.getDisplayName());
        values.put(AUTHENTICATION_URL, identityProvider.getAuthenticationURL());
        values.put(OCRA_SUITE, identityProvider.getOCRASuite());
        values.put(LOGO, identityProvider.getLogoData());
        values.put(INFO_URL, identityProvider.getInfoURL());
        values.put(VERSION, identityProvider.getVersion());

        return _db.update(TABLE_IDENTITYPROVIDER, values, ROWID + " = ?", new String[] { String.valueOf(identityProvider.getId()) }) > 0;
    }

    /**
     * Deletes a particular identity provider.
     * 
     * @param long identityProviderId The identity provider row-id
     * 
     * @return delete successful?
     */
    public boolean deleteIdentityProvider(long identityProviderId) {
        return _db.delete(TABLE_IDENTITYPROVIDER, ROWID + " = ?", new String[] { String.valueOf(identityProviderId) }) > 0;
    }

    /**
     * Create identity provider objects for the results of the given cursor.
     * 
     * NOTE: this method closes the cursor when it's done!
     * 
     * @param cursor database cursor
     * 
     * @return IdentityProvider objects
     */
    private IdentityProvider[] _createIdentityProviderObjectsForCursor(Cursor cursor) {
        ArrayList<IdentityProvider> identityproviders = new ArrayList<IdentityProvider>();

        if (cursor.moveToFirst()) {
            int rowIdColumn = cursor.getColumnIndex(DbAdapter.ROWID);
            int identifierColumn = cursor.getColumnIndex(DbAdapter.IDENTIFIER);
            int displayNameColumn = cursor.getColumnIndex(DbAdapter.DISPLAY_NAME);
            int authURLColumn = cursor.getColumnIndex(DbAdapter.AUTHENTICATION_URL);
            int ocraSuiteColumn = cursor.getColumnIndex(DbAdapter.OCRA_SUITE);
            int logoColumn = cursor.getColumnIndex(DbAdapter.LOGO);
            int infoURLColumn = cursor.getColumnIndex(DbAdapter.INFO_URL);
            int versionColumn = cursor.getColumnIndex(DbAdapter.VERSION);

            do {
                IdentityProvider ip = new IdentityProvider();
                ip.setId(cursor.getInt(rowIdColumn));
                ip.setIdentifier(cursor.getString(identifierColumn));
                ip.setDisplayName(cursor.getString(displayNameColumn));
                ip.setAuthenticationURL(cursor.getString(authURLColumn));
                ip.setOCRASuite(cursor.getString(ocraSuiteColumn));
                ip.setLogoData(cursor.getBlob(logoColumn));
                ip.setInfoURL(cursor.getString(infoURLColumn));
                ip.setVersion(cursor.getFloat(versionColumn));
                identityproviders.add(ip);
            } while (cursor.moveToNext());
        }

        cursor.close();

        IdentityProvider[] result = new IdentityProvider[identityproviders.size()];
        return identityproviders.toArray(result);
    }

    /**
     * Returns the identity provider with the given identifier.
     * 
     * @param identifier identity provider identifier
     * 
     * @return cursor object for identity provider
     */
    public Cursor getIdentityProviderByIdentifier(String identifier) throws SQLException {
        Cursor cursor = _db.query(TABLE_IDENTITYPROVIDER, new String[] { ROWID, DISPLAY_NAME, IDENTIFIER, AUTHENTICATION_URL, OCRA_SUITE, LOGO, INFO_URL, VERSION }, IDENTIFIER + " = ?", new String[] { identifier }, null, null, null);

        if (cursor != null) {
            cursor.moveToFirst();
        }

        return cursor;
    }

    /**
     * Return the identity provider for a given identity (identified by its id)
     * 
     * @param identity_id The identity id
     * @return
     */
    public IdentityProvider getIdentityProviderForIdentityId(long identity_id) {
        SQLiteQueryBuilder builder = new SQLiteQueryBuilder();
        builder.setTables(JOIN_IDENTITY_IDENTITYPROVIDER);

        Cursor cursor = builder.query(_db, new String[] { TABLE_IDENTITYPROVIDER + "." + ROWID, TABLE_IDENTITYPROVIDER + "." + DISPLAY_NAME, TABLE_IDENTITYPROVIDER + "." + IDENTIFIER, TABLE_IDENTITYPROVIDER + "." + AUTHENTICATION_URL,
                TABLE_IDENTITYPROVIDER + "." + OCRA_SUITE, TABLE_IDENTITYPROVIDER + "." + INFO_URL, TABLE_IDENTITYPROVIDER + "." + LOGO, TABLE_IDENTITYPROVIDER + "." + VERSION, }, TABLE_IDENTITY + "." + ROWID + " = ?",
                new String[] { String.valueOf(identity_id) }, null, null, SORT_INDEX);

        IdentityProvider[] identityProviders = _createIdentityProviderObjectsForCursor(cursor);
        if (identityProviders.length > 0) {
            return identityProviders[0];
        }

        return null;
    }

    /**
     * Returns the identity provider with the given identifier as an identityprovider object.
     * 
     * @param identifier identity provider identifier
     * 
     * @return identity provider object or null if identity provider is unknown
     */
    public IdentityProvider getIdentityProviderByIdentifierAsObject(String identifier) {
        try {
            IdentityProvider[] identityproviders = _createIdentityProviderObjectsForCursor(getIdentityProviderByIdentifier(identifier));
            return identityproviders.length == 1 ? identityproviders[0] : null;
        } catch (Exception ex) {
            ex.printStackTrace();
            return null;
        }
    }
}