package org.tiqr.authenticator.protection;

import javax.crypto.SecretKey;

public interface SessionKeyAvailabilityListener
{
    public void onSessionKeyAvailable(SecretKey sessionKey);
}
