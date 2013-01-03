package org.tiqr.authenticator.auth;

import java.io.BufferedInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.net.MalformedURLException;
import java.net.URL;
import java.net.URLConnection;

import org.apache.http.util.ByteArrayBuffer;
import org.json.JSONException;
import org.json.JSONObject;
import org.json.JSONTokener;
import org.tiqr.authenticator.R;
import org.tiqr.authenticator.datamodel.Identity;
import org.tiqr.authenticator.datamodel.IdentityProvider;
import org.tiqr.authenticator.exceptions.UserException;

import android.content.Context;
import android.util.Log;

/**
 * Represents an enrollment challenge.
 */
public class EnrollmentChallenge extends Challenge {
    private String _enrollmentURL;
    private boolean _allowLocalFiles;

    /**
     * Constructs a new enrollment challenge with the given raw challenge.
     * 
     * The raw challenge is immediately parsed, an exception is thrown when an error occurs.
     * 
     * @param rawChallenge raw challenge
     * @param context Android context
     * 
     * @throws Exception
     */
    public EnrollmentChallenge(String rawChallenge, Context context) throws UserException {
        this(rawChallenge, context, false);
    }

    /**
     * Constructs a new enrollment challenge with the given raw challenge.
     * 
     * The raw challenge is immediately parsed, an exception is thrown when an error occurs.
     * 
     * @param rawChallenge raw challenge
     * @param context Android context
     * @param boolean allow local files to be used for the challenge URL and identity provider logo?
     * 
     * @throws Exception
     */
    public EnrollmentChallenge(String rawChallenge, Context context, boolean allowLocalFiles) throws UserException {
        super(rawChallenge, context, false);
        _allowLocalFiles = allowLocalFiles;
        _parseRawChallenge();
    }

    /**
     * Enrollment callback URL.
     * 
     * @return enrollment callback URL.
     */
    public String getEnrollmentURL() {
        return _enrollmentURL;
    }

    /**
     * Download data from the given URL (synchronously).
     * 
     * @param url url
     * 
     * @return data
     */
    private byte[] _downloadSynchronously(URL url) throws IOException {
        URLConnection connection = url.openConnection();
        InputStream inputStream = connection.getInputStream();
        BufferedInputStream bufferedInputStream = new BufferedInputStream(inputStream);
        ByteArrayBuffer buffer = new ByteArrayBuffer(50);

        int current = 0;
        while ((current = bufferedInputStream.read()) != -1) {
            buffer.append((byte)current);
        }

        return buffer.toByteArray();
    }

    /**
     * Returns a identity provider object based on the given metadata. If the identity provider already exists, the existing identity provider object is
     * returned, else a new one is created.
     * 
     * @param metadata JSON identity provider metadata
     * 
     * @return IdentityProvider object
     * 
     * @throws Exception
     */
    private IdentityProvider _getIdentityProviderForMetadata(JSONObject metadata) throws JSONException, UserException {
        IdentityProvider ip = _getDbAdapter().getIdentityProviderByIdentifierAsObject(metadata.getString("identifier"));
        if (ip == null) {
            ip = new IdentityProvider();
            ip.setIdentifier(metadata.getString("identifier"));
            ip.setDisplayName(metadata.getString("displayName"));
            ip.setAuthenticationURL(metadata.getString("authenticationUrl"));
            ip.setInfoURL(metadata.getString("infoUrl"));
            try {
                ip.setVersion(Float.parseFloat(metadata.getString("version")));
            } catch (NumberFormatException e) {
                ip.setVersion(0.0f);
            }
            if (metadata.has("ocraSuite")) {
                ip.setOCRASuite(metadata.getString("ocraSuite"));
            }
            try {
                URL logoURL = new URL(metadata.getString("logoUrl"));
                byte[] logoData = _downloadSynchronously(logoURL);
                ip.setLogoData(logoData);
            } catch (Exception ex) {
                throw new UserException(_getString(R.string.error_enroll_logo_error), ex);
            }

            if (ip.getLogoBitmap() == null) {
                throw new UserException(_getString(R.string.error_enroll_logo_error));
            }
        }

        return ip;
    }

    /**
     * Returns an identity object based on the given metadata. If the identity already exists an exception is thrown.
     * 
     * @param metadata JSON identity metadata
     * 
     * @return identity object
     * 
     * @throws Exception
     */
    private Identity _getIdentityForMetadata(JSONObject metadata, IdentityProvider ip) throws JSONException, UserException {
        Identity identity = _getDbAdapter().getIdentityByIdentifierAndIdentityProviderIdAsObject(metadata.getString("identifier"), ip.getId());
        if (identity != null) {
            Object[] args = new Object[] { metadata.getString("displayName"), ip.getDisplayName() };
            throw new UserException(_getString(R.string.error_enroll_already_enrolled, args));
        }

        identity = new Identity();
        identity.setIdentifier(metadata.getString("identifier"));
        identity.setDisplayName(metadata.getString("displayName"));
        return identity;
    }

    /**
     * Parses the raw authentication challenge.
     * 
     * @throws Exception
     */
    @Override
    protected void _parseRawChallenge() throws UserException {
        if (!_getRawChallenge().startsWith("tiqrenroll://")) {
            throw new UserException(_getString(R.string.error_enroll_invalid_qr_code));
        }

        URL url;
        try {
            url = new URL(_getRawChallenge().substring(13));
        } catch (MalformedURLException ex) {
            throw new UserException(_getString(R.string.error_enroll_invalid_qr_code));
        }

        if (!url.getProtocol().equals("http") && !url.getProtocol().equals("https") && !url.getProtocol().equals("file")) {
            throw new UserException(_getString(R.string.error_enroll_invalid_qr_code));
        } else if (url.getProtocol().equals("file") && !_allowLocalFiles) {
            throw new UserException(_getString(R.string.error_enroll_invalid_qr_code));
        }

        JSONObject metadata;

        try {
            byte[] data = _downloadSynchronously(url);
            String json = new String(data);
            Log.d(getClass().getSimpleName(), "Enrollment server response: " + json);
            JSONTokener tokener = new JSONTokener(json);
            Object value = tokener.nextValue();
            if (!(value instanceof JSONObject)) {
                throw new UserException(_getString(R.string.error_enroll_invalid_response));
            }

            metadata = (JSONObject)value;
            _enrollmentURL = metadata.getJSONObject("service").getString("enrollmentUrl");
            _setReturnURL(null); // TODO: FIXME
            _setIdentityProvider(_getIdentityProviderForMetadata(metadata.getJSONObject("service")));
            _setIdentity(_getIdentityForMetadata(metadata.getJSONObject("identity"), getIdentityProvider()));
        } catch (IOException ex) {
            throw new UserException(_getString(R.string.error_enroll_connect_error), ex);
        } catch (JSONException ex) {
            throw new UserException(_getString(R.string.error_enroll_invalid_response), ex);
        }
    }

}