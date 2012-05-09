//
//  AuthenticationChallengeTests.h
//  MobileAuth
//
//  Created by Peter Verhage on 17-03-11.
//  Copyright 2011 Egeniq. All rights reserved.

#import <SenTestingKit/SenTestingKit.h>
#import <UIKit/UIKit.h>
#import <CoreData/CoreData.h>

@interface AuthenticationChallengeTests : SenTestCase {
    NSManagedObjectContext *managedObjectContext_;
}

- (void)testInvalidChallenges;
- (void)testBasicChallenges;
- (void)testIdentityChallenges;
- (void)testReturnURLChallenges;

@end
