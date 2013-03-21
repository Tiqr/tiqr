/*
 * Copyright (c) 2010-2011 SURFnet bv
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. Neither the name of SURFnet bv nor the names of its contributors 
 *    may be used to endorse or promote products derived from this 
 *    software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
 * IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 * GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER
 * IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
 * OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN
 * IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

#import <CommonCrypto/CommonCryptor.h>

#define kChosenCipherKeySize kCCKeySizeAES256

/**
 * Helper object for storing and retrieving a secret for a
 * certain identity to and from the Keychain.
 */
@interface SecretStore : NSObject {
    NSData *encryptedSecret_;
}

/**
 * Identity identifier.
 */
@property (nonatomic, readonly) NSString *identityIdentifier;

/**
 * Identity provider identifier.
 */
@property (nonatomic, readonly) NSString *identityProviderIdentifier;

/**
 * Constructs a new secret store object for the given identity and provider.
 *
 * @param identityIdentifier          identity identifier
 * @param identityProviderIdentifier  identity provider identifier
 *
 * @return a new Secret instance
 */
+ (SecretStore *)secretStoreForIdentity:(NSString *)identityIdentifier identityProvider:(NSString *)identityProviderIdentifier;

/**
 * Generate a new random secret.
 *
 * @return new random secret data
 */
+ (NSData *)generateSecret;

/**
 * Sets the secret, encrypted with the given PIN.
 *
 * @param secret secret
 * @param PIN    PIN
 * @param salt   salt
 * @param initializationVector  initializationVector
 */
- (void)setSecret:(NSData *)secret PIN:(NSString *)PIN salt:(NSData *)salt initializationVector:(NSData *)initializationVector;

/**
 * Returns the decrypted secret, decrypted with the given PIN.
 *
 * There is no way in telling if the PIN was correct or not.
 *
 * @param PIN PIN
 * @param salt   salt
 * @param initializationVector  initializationVector
 *
 * @return decrypted secret
 */
- (NSData *)secretForPIN:(NSString *)PIN salt:(NSData *)salt initializationVector:(NSData *)initializationVector;

/**
 * Stores the (new/updated) secret for this identity in the Keychain.
 *
 * @return whatever storing the secret was successful or not
 */
- (BOOL)storeInKeychain;

/**
 * Delets the secret for this identity from the Keychain.
 *
 * @return whatever deleting the secret was successful or not
 */
- (BOOL)deleteFromKeychain;

@end