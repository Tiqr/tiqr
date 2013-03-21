//
//  EnrollmentChallengeTests.m
//  MobileAuth
//
//  Created by Peter Verhage on 17-03-11.
//  Copyright 2011 Egeniq. All rights reserved.
//

#import "EnrollmentChallengeTests.h"
#import "EnrollmentChallenge.h"
#import "JSONKit.h"

@implementation EnrollmentChallengeTests

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
    IdentityProvider *service = [NSEntityDescription insertNewObjectForEntityForName:@"Service" inManagedObjectContext:managedObjectContext_];
    service.identifier = @"example.org";
    service.displayName = @"Dummy Service";
    service.authenticationUrl = @"http://example.org/auth/";
    
    Identity *identity = [NSEntityDescription insertNewObjectForEntityForName:@"Identity" inManagedObjectContext:managedObjectContext_];
    identity.identityProvider = service;
    identity.identifier = @"john.doe";
    identity.displayName = @"John Doe";
    identity.sortIndex = [NSNumber numberWithInt:1];
    
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


/**
 * Creates an enrollment challenge object for the given JSON string. 
 * 
 * The given JSON string is saved to a file, which name is passed as the raw challenge
 * to the EnrollmentChallenge constructor. 
 */
- (EnrollmentChallenge *)createEnrollmentChallenge:(NSString *)challengeJSON {
    NSURL *tempURL = [[NSURL alloc] initFileURLWithPath:NSTemporaryDirectory() isDirectory:YES];
    NSURL *fileURL = [tempURL URLByAppendingPathComponent:@"challenge.txt"];
    [tempURL release];
    
    NSError *error = nil;
    [challengeJSON writeToURL:fileURL atomically:YES encoding:NSUTF8StringEncoding error:&error];
    STAssertNil(error, @"Should be nil");
    NSString *rawChallenge = [NSString stringWithFormat:@"surfenroll://%@", [fileURL absoluteString]];
    EnrollmentChallenge *challenge = [[EnrollmentChallenge alloc] initWithRawChallenge:rawChallenge managedObjectContext:managedObjectContext_ allowFiles:YES];
    return [challenge autorelease];
}


- (void)testInvalidChallenges {
    // invalid qr start
    EnrollmentChallenge *challenge = [[EnrollmentChallenge alloc] initWithRawChallenge:@"surfauth://http://example.org" managedObjectContext:managedObjectContext_ allowFiles:YES];
    STAssertFalse(challenge.valid, @"Should be false");
    STAssertEqualObjects(@"Invalid enrollment QR code. Please contact the website administrator.", challenge.error.localizedDescription, @"Should be equal");
    [challenge release];
    
    // invalid protocol
    challenge = [[EnrollmentChallenge alloc] initWithRawChallenge:@"surfenroll://xyz://example.org" managedObjectContext:managedObjectContext_ allowFiles:YES];
    STAssertFalse(challenge.valid, @"Should be false");
    STAssertEqualObjects(@"Invalid enrollment QR code. Please contact the website administrator.", challenge.error.localizedDescription, @"Should be equal");
    [challenge release];

    // no files allowed (default)
    challenge = [[EnrollmentChallenge alloc] initWithRawChallenge:@"surfenroll://file:something" managedObjectContext:managedObjectContext_];
    STAssertFalse(challenge.valid, @"Should be false");
    STAssertEqualObjects(@"Invalid enrollment QR code. Please contact the website administrator.", challenge.error.localizedDescription, @"Should be equal");
    [challenge release];

    // files allowed, but non existing
    challenge = [[EnrollmentChallenge alloc] initWithRawChallenge:@"surfenroll://file:does-not-exist" managedObjectContext:managedObjectContext_ allowFiles:YES];
    STAssertFalse(challenge.valid, @"Should be false");
    STAssertEqualObjects(@"Cannot connect to enrollment server. Please contact the website administrator.", challenge.error.localizedDescription, @"Should be equal");
    [challenge release];
    
    
    NSString *logoURL = @"http://www.surfnet.nl/Style%20Library/SURFnet/img/surfnet_logo.gif";
    
    challenge = [self createEnrollmentChallenge:@"..."];
    STAssertFalse(challenge.valid, @"Should be false");
    STAssertEqualObjects(@"Invalid enrollment server response. Please contact the website administrator.", challenge.error.localizedDescription, @"Should be equal");
    
    challenge = [self createEnrollmentChallenge:@"{}"];
    STAssertFalse(challenge.valid, @"Should be false");
    STAssertEqualObjects(@"Invalid enrollment server response. Please contact the website administrator.", challenge.error.localizedDescription, @"Should be equal");
    
    challenge = [self createEnrollmentChallenge:[NSString stringWithFormat:@"{ \"service\": { \"identifier\": \"example.org\", \"displayName\": \"Dummy Service\", \"logoUrl\": \"%@\", \"authenticationUrl\": \"\" }, \"identity\": { \"identifier\": \"john.doe\", \"displayName\": \"John Doe\" } }", logoURL]];
    STAssertFalse(challenge.valid, @"Should be false");
    STAssertEqualObjects(@"Invalid enrollment server response. Please contact the website administrator.", challenge.error.localizedDescription, @"Should be equal");

    challenge = [self createEnrollmentChallenge:[NSString stringWithFormat:@"{ \"service\": { \"identifier\": \"example.org\", \"displayName\": \"Dummy Service\", \"logoUrl\": \"%@\", \"authenticationUrl\": \"\", \"enrollmentUrl\": \"http://example.org/enroll/\" } }", logoURL]];
    STAssertFalse(challenge.valid, @"Should be false");
    STAssertEqualObjects(@"Invalid enrollment server response. Please contact the website administrator.", challenge.error.localizedDescription, @"Should be equal");

    challenge = [self createEnrollmentChallenge:@"{ \"identity\": { \"identifier\": \"john.doe\", \"displayName\": \"John Doe\" } }"];
    STAssertFalse(challenge.valid, @"Should be false");
    STAssertEqualObjects(@"Invalid enrollment server response. Please contact the website administrator.", challenge.error.localizedDescription, @"Should be equal");

    challenge = [self createEnrollmentChallenge:@"{ \"service\": { \"identifier\": \"other.org\", \"displayName\": \"Other Dummy Service\", \"logoUrl\": \"\", \"authenticationUrl\": \"\", \"enrollmentUrl\": \"http://example.org/enroll/\" }, \"identity\": { \"identifier\": \"jane.doe\", \"displayName\": \"Jane Doe\" } }"];
    STAssertFalse(challenge.valid, @"Should be false");
    STAssertEqualObjects(@"Cannot download service logo. Please contact the website administrator.", challenge.error.localizedDescription, @"Should be equal");    
    
    
    
    //try {
    //    _createEnrollmentChallenge("{ service: { identifier: 'example.org', displayName: 'Dummy Service', logoUrl: '" + logoURL + "', authenticationUrl: '', enrollmentUrl: 'http://example.org/enroll/' }, identity: { identifier: 'john.doe', displayName: 'John Doe' } }");            
   // }
    //catch (UserException ex) {
    //    assertEquals(getContext().getString(R.string.error_enroll_already_enrolled, new Object[] { "John Doe", "Dummy Service" } ), ex.getMessage());            
    //}        
}

- (void)testChallengeWithNewService {
    
}

- (void)testChallengeWithExistingService {
    
}

@end