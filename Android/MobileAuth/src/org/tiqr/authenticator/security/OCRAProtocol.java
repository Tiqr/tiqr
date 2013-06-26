package org.tiqr.authenticator.security;

import org.tiqr.authenticator.exceptions.InvalidChallengeException;

public interface OCRAProtocol {
    public abstract String generateOCRA(String ocraSuite, byte[] key, String challengeQuestion, String sessionKey) throws InvalidChallengeException;
}
