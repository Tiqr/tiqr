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

#import "SecretStore.h"

#import <Security/Security.h>
#import <CommonCrypto/CommonHMAC.h>

@implementation SecretStore

@synthesize identityIdentifier=identityIdentifier_, identityProviderIdentifier=identityProviderIdentifier_;

- (NSData *)loadFromKeychain {
	NSMutableDictionary *query = [[NSMutableDictionary alloc] init];
	[query setObject:(id)kSecClassGenericPassword forKey:(id)kSecClass];
	[query setObject:self.identityProviderIdentifier forKey:(id)kSecAttrService];
	[query setObject:self.identityIdentifier forKey:(id)kSecAttrAccount];		
	[query setObject:(id)kSecMatchLimitOne forKey:(id)kSecMatchLimit];
    [query setObject:(id)kCFBooleanTrue forKey:(id)kSecReturnData];	
	[query setObject:(id)kCFBooleanTrue forKey:(id)kSecReturnAttributes];
	
	NSDictionary *result = nil;
	if (SecItemCopyMatching((CFDictionaryRef)query, (CFTypeRef *)&result) == noErr) {
		return (NSData *)[result objectForKey:(id)kSecValueData];
	} else {
		return nil;
	}
}

- (BOOL)addToKeychain {
	NSMutableDictionary *data = [[NSMutableDictionary alloc] init];	
	[data setObject:(id)kSecClassGenericPassword forKey:(id)kSecClass];
	[data setObject:self.identityProviderIdentifier forKey:(id)kSecAttrService];
	[data setObject:self.identityIdentifier forKey:(id)kSecAttrAccount];		
    [data setObject:encryptedSecret_ forKey:(id)kSecValueData];	
	
	NSMutableDictionary *result = nil;
	return SecItemAdd((CFDictionaryRef)data, (CFTypeRef *)&result) == noErr;
}

- (BOOL)updateInKeychain {
	NSMutableDictionary *query = [[NSMutableDictionary alloc] init];
	[query setObject:(id)kSecClassGenericPassword forKey:(id)kSecClass];
	[query setObject:self.identityProviderIdentifier forKey:(id)kSecAttrService];
	[query setObject:self.identityIdentifier forKey:(id)kSecAttrAccount];
	
	NSMutableDictionary *data = [[NSMutableDictionary alloc] init];
	[data setObject:encryptedSecret_ forKey:(id)kSecValueData];
	
	return SecItemUpdate((CFDictionaryRef)query, (CFDictionaryRef)data) == noErr;
}

- (BOOL)storeInKeychain {
	if ([self loadFromKeychain] == nil) {
		return [self addToKeychain];
	} else {
		return [self updateInKeychain];
	}
}

- (BOOL)deleteFromKeychain {
	NSMutableDictionary *query = [[NSMutableDictionary alloc] init];
	[query setObject:(id)kSecClassGenericPassword forKey:(id)kSecClass];
	[query setObject:self.identityProviderIdentifier forKey:(id)kSecAttrService];
	[query setObject:self.identityIdentifier forKey:(id)kSecAttrAccount];
	
	return SecItemDelete((CFDictionaryRef)query) == noErr;
}

- (id)initWithIdentity:(NSString *)identityIdentifier identityProvider:(NSString *)identityProviderIdentifier {
	if ((self = [super init]) != nil) {
		identityIdentifier_ = [identityIdentifier copy];
		identityProviderIdentifier_ = [identityProviderIdentifier copy];
		encryptedSecret_ = [[self loadFromKeychain] retain];
	}
	
	return self;
}

- (NSString *)keyForPIN:(NSString *)PIN {
    return PIN;
}

- (NSData *)encrypt:(NSData *)data key:(NSString *)key {
    // 'key' should be 32 bytes for AES256, will be null-padded otherwise
    char keyBuffer[kChosenCipherKeySize + 1]; // room for terminator (unused)
    bzero(keyBuffer, sizeof(keyBuffer)); // fill with zeros (for padding)

    // fetch key data
    [key getCString:keyBuffer maxLength:sizeof(keyBuffer) encoding:NSASCIIStringEncoding];

    // For block ciphers, the output size will always be less than or 
	// equal to the input size plus the size of one block.
	// That's why we need to add the size of one block here.
    size_t bufferSize = [data length] + kCCBlockSizeAES128;
	void *buffer = malloc(bufferSize); 

    // encrypt
    size_t numBytesEncrypted = 0;
    CCCryptorStatus result = CCCrypt(kCCEncrypt, 
                                     kCCAlgorithmAES128, 
                                     0, 
                                     keyBuffer, 
                                     kChosenCipherKeySize,
                                     NULL, // initialization vector (optional)
                                     [data bytes], // input
                                     [data length],
                                     buffer, // output
                                     bufferSize,
                                     &numBytesEncrypted);

    if (result == kCCSuccess) {
        // the returned NSData takes ownership of the buffer and will free it on deallocation        
        return [NSData dataWithBytesNoCopy:buffer length:numBytesEncrypted];
    }
    
    free(buffer);
    return nil;
}

- (NSData *)decrypt:(NSData *)data key:(NSString *)key {
    // 'key' should be 32 bytes for AES256, will be null-padded otherwise
    char keyBuffer[kChosenCipherKeySize + 1]; // room for terminator (unused)
    bzero(keyBuffer, sizeof(keyBuffer)); // fill with zeros (for padding)
    
    // fetch key data
    [key getCString:keyBuffer maxLength:sizeof(keyBuffer) encoding:NSUTF8StringEncoding];
    
    // For block ciphers, the output size will always be less than or 
	// equal to the input size plus the size of one block.
	// That's why we need to add the size of one block here.
    size_t bufferSize = [data length] + kCCBlockSizeAES128;
	void *buffer = malloc(bufferSize);  

    // decrypt
    size_t numBytesDecrypted = 0;
    CCCryptorStatus result = CCCrypt(kCCDecrypt, 
                                     kCCAlgorithmAES128, 
                                     0, 
                                     keyBuffer, 
                                     kChosenCipherKeySize,
                                     NULL, // initialization vector (optional)
                                     [data bytes], // input
                                     [data length],
                                     buffer, // output
                                     bufferSize,
                                     &numBytesDecrypted);
    
    if (result == kCCSuccess) {
        // the returned NSData takes ownership of the buffer and will free it on deallocation        
        return [NSData dataWithBytesNoCopy:buffer length:numBytesDecrypted];
    }
    
    free(buffer);
    return nil;
}

- (void)setSecret:(NSData *)secret PIN:(NSString *)PIN {
    [encryptedSecret_ release];
    NSString *key = [self keyForPIN:PIN];    
    encryptedSecret_ = [[self encrypt:secret key:key] retain];
}

- (NSData *)secretForPIN:(NSString *)PIN {
    if (encryptedSecret_ == nil) {
        return nil;
    }
    
    NSString *key = [self keyForPIN:PIN];
    NSData *result = [self decrypt:encryptedSecret_ key:key];
    return result;
}

- (void)dealloc {
	[encryptedSecret_ release];
    encryptedSecret_ = nil;
	
	[identityProviderIdentifier_ release];
	identityProviderIdentifier_ = nil;
	
	[identityIdentifier_ release];
	identityIdentifier_ = nil;
	
    [super dealloc];
}

+ (SecretStore *)secretStoreForIdentity:(NSString *)identityIdentifier identityProvider:(NSString *)identityProviderIdentifier {
	return [[[SecretStore alloc] initWithIdentity:identityIdentifier identityProvider:identityProviderIdentifier] autorelease];
}

+ (NSData *)generateSecret {
	uint8_t *bytes = malloc(kChosenCipherKeySize * sizeof(uint8_t));
	memset((void *)bytes, 0x0, kChosenCipherKeySize);
	OSStatus sanityCheck = SecRandomCopyBytes(kSecRandomDefault, kChosenCipherKeySize, bytes);
	if (sanityCheck == noErr) {
		NSData *secret = [[[NSData alloc] initWithBytes:(const void *)bytes length:kChosenCipherKeySize] autorelease];
		return secret;
	} else {
		return nil;
	}
}

@end