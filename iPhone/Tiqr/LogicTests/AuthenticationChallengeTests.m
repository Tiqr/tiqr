//
//  AuthenticationChallengeTests.m
//  MobileAuth
//
//  Created by Peter Verhage on 17-03-11.
//  Copyright 2011 Egeniq. All rights reserved.
//

#import "AuthenticationChallengeTests.h"
#import "AuthenticationChallenge.h"

@implementation AuthenticationChallengeTests

- (NSManagedObjectModel *)managedObjectModel {
    NSArray *bundles = [NSArray arrayWithObject:[NSBundle bundleForClass:[self class]]];
    return [NSManagedObjectModel mergedModelFromBundles:bundles];
}

- (NSPersistentStoreCoordinator *)persistentStoreCoordinator {
    NSError *error = nil;
    NSPersistentStoreCoordinator *persistentStoreCoordinator = [[[NSPersistentStoreCoordinator alloc] initWithManagedObjectModel:[self managedObjectModel]] autorelease];
    if (![persistentStoreCoordinator addPersistentStoreWithType:NSInMemoryStoreType configuration:nil URL:nil options:nil error:&error]) {
        return nil;
    } else {
        return persistentStoreCoordinator;
    }
}

- (NSManagedObjectContext *)managedObjectContext {
    NSPersistentStoreCoordinator *coordinator = [self persistentStoreCoordinator];
    if (coordinator != nil) {
        NSManagedObjectContext *managedObjectContext = [[NSManagedObjectContext alloc] init];
        [managedObjectContext setPersistentStoreCoordinator:coordinator];
        return managedObjectContext;
    } else {
        return nil;
    }
}

- (void)insertData {
    IdentityProvider *service1 = [NSEntityDescription insertNewObjectForEntityForName:@"IdentityProvider" inManagedObjectContext:managedObjectContext_];
    service1.identifier = @"one.example.org";
    service1.displayName = @"Dummy IdentityProvider 1";
    service1.authenticationUrl = @"http://one.example.org/auth/";
    
    Identity *identity1 = [NSEntityDescription insertNewObjectForEntityForName:@"Identity" inManagedObjectContext:managedObjectContext_];
    identity1.identityProvider = service1;
    identity1.identifier = @"john.doe";
    identity1.displayName = @"John Doe";
    identity1.sortIndex = [NSNumber numberWithInt:1];
    
    Identity *identity2 = [NSEntityDescription insertNewObjectForEntityForName:@"Identity" inManagedObjectContext:managedObjectContext_];
    identity2.identityProvider = service1;
    identity2.identifier = @"jane.doe";
    identity2.displayName = @"Jane Doe";
    identity2.sortIndex = [NSNumber numberWithInt:2];
    
    IdentityProvider *service2 = [NSEntityDescription insertNewObjectForEntityForName:@"IdentityProvider" inManagedObjectContext:managedObjectContext_];
    service2.identifier = @"two.example.org";
    service2.displayName = @"Dummy IdentityProvider 2";
    service2.authenticationUrl = @"http://two.example.org/auth/";
    
    Identity *identity3 = [NSEntityDescription insertNewObjectForEntityForName:@"Identity" inManagedObjectContext:managedObjectContext_];
    identity3.identityProvider = service2;
    identity3.identifier = @"john.doe";
    identity3.displayName = @"John Doe";
    identity3.sortIndex = [NSNumber numberWithInt:3];

    IdentityProvider *service3 = [NSEntityDescription insertNewObjectForEntityForName:@"IdentityProvider" inManagedObjectContext:managedObjectContext_];
    service3.identifier = @"three.example.org";
    service3.displayName = @"Dummy IdentityProvider 3";
    service3.authenticationUrl = @"http://three.example.org/auth/";
    
    NSError *error = nil;
    STAssertTrue([managedObjectContext_ save:&error], @"Should be true");
    STAssertNil(error, @"Should be nil");
}

- (void)setUp {
    [super setUp];
    managedObjectContext_ = [self managedObjectContext];
    [self insertData];
}

- (void)tearDown {
    [super tearDown];
    [managedObjectContext_ release];
    managedObjectContext_ = nil;
}

- (void)assertInvalidChallenge:(NSString *)rawChallenge errorMessage:(NSString *)errorMessage {
    AuthenticationChallenge *challenge = [[AuthenticationChallenge alloc] initWithRawChallenge:rawChallenge managedObjectContext:managedObjectContext_];    
    STAssertNotNil(challenge, @"Challenge should never be nil");
    STAssertFalse(challenge.valid, @"Challenge should be invalid");
    STAssertEqualObjects(errorMessage, challenge.error.localizedDescription, @"Error message should be \"%@\"", errorMessage);
    [challenge release];    
}

- (void)testInvalidChallenges {
    [self assertInvalidChallenge:@"http://one.example.org/" errorMessage:@"Invalid authentication QR code."];
    [self assertInvalidChallenge:@"surfauth://one.example.org/" errorMessage:@"Invalid authentication QR code."];
    [self assertInvalidChallenge:@"surfauth://four.example.org/sessionKey/challenge" errorMessage:@"Unknown service, please enroll first."];
    [self assertInvalidChallenge:@"surfauth://invalid@one.example.org/sessionKey/challenge" errorMessage:@"Unknown identity for service, please enroll first."];
    [self assertInvalidChallenge:@"surfauth://three.example.org/sessionKey/challenge" errorMessage:@"No identities found for service, please enroll first."];
}

- (void)testBasicChallenges {
    AuthenticationChallenge *challenge = [[AuthenticationChallenge alloc] initWithRawChallenge:@"surfauth://one.example.org/sessionKey/challenge" managedObjectContext:managedObjectContext_];
    STAssertTrue(challenge.valid, @"Should be true");
    STAssertNotNil(challenge.identityProvider, @"Should not be nil");
    STAssertEqualObjects(@"one.example.org", challenge.identityProvider.identifier, @"Should be equal");
    STAssertEqualObjects(@"Dummy IdentityProvider 1", challenge.identityProvider.displayName, @"Should be equal");
    STAssertNil(challenge.identity, @"Should be nil");
    STAssertEqualObjects(@"sessionKey", challenge.sessionKey, @"Should be equal");
    STAssertEqualObjects(@"challenge", challenge.challenge, @"Should be equal");      
    STAssertNil(challenge.returnUrl, @"Should be nil");
    [challenge release];
    
    challenge = [[AuthenticationChallenge alloc] initWithRawChallenge:@"surfauth://two.example.org/sessionKey/challenge" managedObjectContext:managedObjectContext_];
    STAssertTrue(challenge.valid, @"Should be true");
    STAssertNotNil(challenge.identityProvider, @"Should not be nil");
    STAssertEqualObjects(@"two.example.org", challenge.identityProvider.identifier, @"Should be equal");
    STAssertEqualObjects(@"Dummy IdentityProvider 2", challenge.identityProvider.displayName, @"Should be equal");
    STAssertNotNil(challenge.identity, @"Should not be nil");
    STAssertEqualObjects(@"John Doe", challenge.identity.displayName, @"Should be equal");
    STAssertEqualObjects(@"sessionKey", challenge.sessionKey, @"Should be equal");
    STAssertEqualObjects(@"challenge", challenge.challenge, @"Should be equal");
    STAssertNil(challenge.returnUrl, @"Should be nil");        
    [challenge release];    
}

- (void)testIdentityChallenges {
    AuthenticationChallenge *challenge = [[AuthenticationChallenge alloc] initWithRawChallenge:@"surfauth://jane.doe@one.example.org/sessionKey/challenge" managedObjectContext:managedObjectContext_];
    STAssertTrue(challenge.valid, @"Should be true");
    STAssertNotNil(challenge.identityProvider, @"Should not be nil");
    STAssertEqualObjects(@"one.example.org", challenge.identityProvider.identifier, @"Should be equal");
    STAssertNotNil(challenge.identity, @"Should not be nil");
    STAssertEqualObjects(@"jane.doe", challenge.identity.identifier, @"Should be equal");
    STAssertEqualObjects(@"Jane Doe", challenge.identity.displayName, @"Should be equal");      
    [challenge release];
    
    challenge = [[AuthenticationChallenge alloc] initWithRawChallenge:@"surfauth://john.doe@two.example.org/sessionKey/challenge" managedObjectContext:managedObjectContext_];
    STAssertTrue(challenge.valid, @"Should be true");
    STAssertNotNil(challenge.identityProvider, @"Should not be nil");
    STAssertEqualObjects(@"two.example.org", challenge.identityProvider.identifier, @"Should be equal");
    STAssertEqualObjects(@"Dummy IdentityProvider 2", challenge.identityProvider.displayName, @"Should be equal");
    STAssertEqualObjects(@"john.doe", challenge.identity.identifier, @"Should be equal");
    STAssertEqualObjects(@"John Doe", challenge.identity.displayName, @"Should be equal");      
    [challenge release];     
}

- (void)testReturnURLChallenges {
    AuthenticationChallenge *challenge = [[AuthenticationChallenge alloc] initWithRawChallenge:@"surfauth://one.example.org/sessionKey/challenge?http%3A%2F%2Fexample.org" managedObjectContext:managedObjectContext_];
    STAssertTrue(challenge.valid, @"Should be true");
    STAssertNotNil(challenge.identityProvider, @"Should not be nil");
    STAssertEqualObjects(@"one.example.org", challenge.identityProvider.identifier, @"Should be equal");
    STAssertNil(challenge.identity, @"Should be nil");
    STAssertEqualObjects(@"sessionKey", challenge.sessionKey, @"Should be equal");
    STAssertEqualObjects(@"challenge", challenge.challenge, @"Should be equal");      
    STAssertEqualObjects(@"http://example.org", challenge.returnUrl, @"Should be equal");
    [challenge release];

    challenge = [[AuthenticationChallenge alloc] initWithRawChallenge:@"surfauth://jane.doe@one.example.org/sessionKey/challenge?http%3A%2F%2Fexample.org%3Fa%3Db" managedObjectContext:managedObjectContext_];
    STAssertTrue(challenge.valid, @"Should be true");
    STAssertNotNil(challenge.identityProvider, @"Should not be nil");
    STAssertEqualObjects(@"one.example.org", challenge.identityProvider.identifier, @"Should be equal");
    STAssertNotNil(challenge.identity, @"Should not be nil");
    STAssertEqualObjects(@"jane.doe", challenge.identity.identifier, @"Should be equal");
    STAssertEqualObjects(@"sessionKey", challenge.sessionKey, @"Should be equal");
    STAssertEqualObjects(@"challenge", challenge.challenge, @"Should be equal");      
    STAssertEqualObjects(@"http://example.org?a=b", challenge.returnUrl, @"Should be equal");
    [challenge release];    
}

@end
