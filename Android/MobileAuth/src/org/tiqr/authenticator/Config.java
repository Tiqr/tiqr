package org.tiqr.authenticator;

import java.io.IOException;
import java.io.InputStream;
import java.util.Properties;

import android.content.Context;

/**
 * Configuration helper.
 */
public class Config {

    private final Context _context;

    /**
     * Constructor.
     */
    public Config(Context context) {
        _context = context;
        _loadConfigurationFromDisk();
    }

    public String getTIQRProtocolVersion() {
        // this client prefers protocol v2. 
        return "2";
    }

    /**
     * Reads the configuration state from disk.
     */
    private void _loadConfigurationFromDisk() {
        Properties defaults = new Properties();

        try {
            InputStream stream = _context.getAssets().open("config/config.properties");
            defaults.load(stream);
            stream.close();
        } catch (IOException e) {
            // should never fail
            throw new RuntimeException(e);
        }

        try {
            InputStream stream = _context.getAssets().open("config/local.properties");
            defaults.load(stream);
            stream.close();
        } catch (IOException e) {
            // might not exist
        }

    }
}
