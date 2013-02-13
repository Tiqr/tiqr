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

#import "OCRAWrapper_v1.h"
#import "NSData+Hex.h"
#import "OCRA_v1.h"

@implementation OCRAWrapper_v1

- (BOOL) shouldIncludeSessionData: (NSString*)ocraSuite {
    
    
    if(([ocraSuite rangeOfString:@":s" options:NSCaseInsensitiveSearch].location != NSNotFound) ||
       ([ocraSuite rangeOfString:@":.*?:.*?\\-s" options:NSCaseInsensitiveSearch|NSRegularExpressionSearch].location != NSNotFound)) {
        
        return YES;
        
    }

    return NO;
}

- (NSString*) numStrToHex: (NSString *)str {
    
    NSDecimalNumber *bigNumberValue = [NSDecimalNumber decimalNumberWithString:str];
    return [NSString stringWithFormat:@"%X", [bigNumberValue intValue]];
}

- (NSString *)generateOCRA:(NSString*)ocraSuite
                    secret: (NSData *)secret 
                 challenge:(NSString*)challengeQuestion
                sessionKey:(NSString*)sessionKey 
                     error:(NSError**)error {
    
    // The reference implementation takes session data into account even if -S isn't specified in the suite. 
    // We therefor explicitly pass "" if -S is not in the suite.
    NSString *sessionData = @"";
    
    if ([self shouldIncludeSessionData:ocraSuite]) {
        sessionData = sessionKey;
    }       
    
    NSString* challenge;

    if ([ocraSuite rangeOfString:@"qn" options:NSCaseInsensitiveSearch].location != NSNotFound) {
        // Using numeric challenge questions, need to convert to hex first
        challenge = [self numStrToHex: challengeQuestion];
    } else {
        // if qh, we're already dealing with hex
        challenge = challengeQuestion;
    }

    return [OCRA_v1 generateOCRA:ocraSuite key:[secret hexStringValue] counter:@"" question:challenge password:@"" sessionInformation:sessionData timestamp:@"" error:error];
}

@end
