//
//  OcraTests.m
//  MobileAuth
//
//  Created by Ivo Jansch on 3/14/11.
//  Copyright 2011 Egeniq. All rights reserved.
//

#import "OcraTests.h"

#import "OCRAWrapper.h"
#import "OCRA.h"

@implementation OcraTests


- (void)testSuiteParsing {
    
    BOOL result = [OCRAWrapper shouldIncludeSessionData:@"OCRA-1:HOTP-SHA1-6:QN08"];
    
    STAssertFalse(result, @"session should not be included");

    result = [OCRAWrapper shouldIncludeSessionData:@"OCRA-1:HOTP-SHA1-6:QN08-S"];
    
    STAssertTrue(result, @"session should be included");
    
    result = [OCRAWrapper shouldIncludeSessionData:@"OCRA-1:HOTP-SHA1-6:S-QN08"];
    
    STAssertTrue(result, @"session should be included");

}

- (void)testPlainChallengeResponse {
    
    NSString *result;
    
    result = [OCRA generateOCRA: @"OCRA-1:HOTP-SHA1-6:QN08"
                            key: @"3132333435363738393031323334353637383930" 
                        counter: @""
                       question: [OCRAWrapper numStrToHex: @"00000000"] 
                       password: @""
             sessionInformation: @""
                      timestamp: @""];
    
    STAssertEqualObjects(@"237653", result, @"Ocra test");
        
    result = [OCRA generateOCRA: @"OCRA-1:HOTP-SHA1-6:QN08"
                            key: @"3132333435363738393031323334353637383930"
                        counter: @"" 
                       question: [OCRAWrapper numStrToHex: @"77777777"]
                       password: @""
             sessionInformation: @""
                      timestamp: @""];
    
    STAssertEqualObjects(@"224598", result, @"Ocra test");
    
}

- (void) testOcraChallengeResponseWithSession { 
    
    NSString *result;
    
    result = [OCRA generateOCRA: @"OCRA-1:HOTP-SHA1-6:QN08-S"
                            key: @"3132333435363738393031323334353637383930"
                        counter: @""
                       question: [OCRAWrapper numStrToHex: @"77777777"]
                       password: @""
             sessionInformation: @"ABCDEFABCDEF"
                      timestamp: @""];
   
    STAssertEqualObjects(@"675831", result, @"Ocra test");
    
}



@end
