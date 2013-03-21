//
//  LogicTests.m
//  LogicTests
//
//  Created by Peter Verhage on 16-02-11.
//  Copyright 2011 __MyCompanyName__. All rights reserved.
//

#import <CommonCrypto/CommonCryptor.h>

#import "SecretStoreTests.h"
#import "SecretStore.h"

@implementation SecretStoreTests

- (void)setUp {
    [super setUp];
}

- (void)tearDown {
    [super tearDown];
}

- (void)testGenerateSecret {
    NSData *secret = [SecretStore generateSecret];
    STAssertNotNil(secret, @"Secret should not be nil");
    STAssertEquals((NSUInteger)kCCKeySizeAES256, secret.length, @"Secret should be %d bytes long, but was %d instead", kCCKeySizeAES256, secret.length);
    
    NSData *anotherSecret = [SecretStore generateSecret];
    STAssertNotNil(anotherSecret, @"Another secret should not be nil");
    STAssertFalse([secret isEqual:anotherSecret], @"Secret should not be equal to another secret");
}

- (void)testSecret {
    SecretStore *store = [SecretStore secretStoreForIdentity:@"john.doe@example.org" identityProvider:@"nl.surfmedia"];
    STAssertNotNil(store, @"Store should not be nil");
    NSData *salt = [SecretStore generateSecret];
    NSData *initializationVector = [SecretStore generateSecret];
    STAssertNil([store secretForPIN:@"12345" salt:salt initializationVector:initializationVector], @"Initial secret should be nil");
    NSData *secret = [SecretStore generateSecret];
    [store setSecret:secret PIN:@"12345" salt:salt initializationVector:initializationVector];
    NSData *retrievedSecret = [store secretForPIN:@"12345" salt:salt initializationVector:initializationVector];
    STAssertNotNil(retrievedSecret, @"Retrieved secret should not be nil when correct PIN is used");    
    STAssertEquals([secret length], [retrievedSecret length], @"Length of secrets should match when correct PIN is used");    
    STAssertEqualObjects(secret, retrievedSecret, @"Retrieved secret should match original secret when correct PIN is used");
    retrievedSecret = [store secretForPIN:@"54321" salt:salt initializationVector:initializationVector];   
    STAssertNotNil(retrievedSecret, @"Retrieved secret should not be nil when wrong PIN is used");    
    STAssertEquals([secret length], [retrievedSecret length], @"Length of secrets should match when wrong PIN is used");    
    STAssertFalse([secret isEqual:retrievedSecret], @"Retrieved secret should not match original secret when wrong PIN is used");
}

@end