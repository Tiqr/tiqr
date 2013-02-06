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

#import "EnrollmentConfirmationRequest.h"
#import "NotificationRegistration.h"
#import "NSData+Hex.h"
#import "JSONKit.h"

NSString *const TIQRECRErrorDomain = @"org.tiqr.ecr";

@interface EnrollmentConfirmationRequest ()

@property (nonatomic, retain) EnrollmentChallenge *challenge;
@property (nonatomic, retain) NSMutableData *data;

@end

@implementation EnrollmentConfirmationRequest

@synthesize delegate=delegate_;
@synthesize challenge=challenge_;
@synthesize data=data_;

- (id)initWithEnrollmentChallenge:(EnrollmentChallenge *)challenge {
    self = [super init];
    if (self != nil) {
        self.challenge = challenge;
    }
    
    return self;
}

- (void)send {
	NSString *secret = [self.challenge.identitySecret hexStringValue];
	NSString *escapedSecret = [secret stringByAddingPercentEscapesUsingEncoding:NSUTF8StringEncoding];
	NSString *escapedLanguage = [[[NSLocale preferredLanguages] objectAtIndex:0] stringByAddingPercentEscapesUsingEncoding:NSUTF8StringEncoding];
	NSString *notificationToken = [NotificationRegistration sharedInstance].notificationToken;
	NSString *escapedNotificationToken = [notificationToken stringByAddingPercentEscapesUsingEncoding:NSUTF8StringEncoding];
    NSString *version = [[NSBundle mainBundle] objectForInfoDictionaryKey:@"TIQRLoginProtocolVersion"];
    NSString *operation = @"register";
	NSString *body = [NSString stringWithFormat:@"secret=%@&language=%@&notificationType=APNS&notificationAddress=%@&version=%@&operation=%@", escapedSecret, escapedLanguage, escapedNotificationToken, version, operation];
    
	NSMutableURLRequest *request = [[NSMutableURLRequest alloc] initWithURL:[NSURL URLWithString:self.challenge.enrollmentUrl]];
	[request setCachePolicy:NSURLRequestReloadIgnoringCacheData];
	[request setTimeoutInterval:5.0];
	[request setHTTPMethod:@"POST"];
	[request setHTTPBody:[body dataUsingEncoding:NSUTF8StringEncoding]];
    
    if (self.challenge.identityProviderTiqrProtocolVersion != nil && [self.challenge.identityProviderTiqrProtocolVersion intValue] >= 1) {
        [request setValue:@"application/json" forHTTPHeaderField:@"Accept"];
    }

    [[NSURLConnection alloc] initWithRequest:request delegate:self];
	self.data = [NSMutableData data];
}

- (void)connection:(NSURLConnection *)connection didReceiveResponse:(NSURLResponse *)response {
    [self.data setLength:0];
}

- (void)connection:(NSURLConnection *)connection didReceiveData:(NSData *)data {
    [self.data appendData:data];
}

- (void)connection:(NSURLConnection *)connection didFailWithError:(NSError *)connectionError {
    [connection release];
    self.data = nil;
    
    NSString *title = NSLocalizedString(@"no_connection", @"No connection error title");
    NSString *message = NSLocalizedString(@"internet_connection_required", @"No connection error message");
    NSMutableDictionary *details = [NSMutableDictionary dictionary];
    [details setValue:title forKey:NSLocalizedDescriptionKey];
    [details setValue:message forKey:NSLocalizedFailureReasonErrorKey];    
    [details setValue:connectionError forKey:NSUnderlyingErrorKey];
    
    NSError *error = [NSError errorWithDomain:TIQRECRErrorDomain code:TIQRECRConnectionError userInfo:details];
    [self.delegate enrollmentConfirmationRequest:self didFailWithError:error];    
}

- (void)connectionDidFinishLoading:(NSURLConnection *)connection {
    if (self.challenge.identityProviderTiqrProtocolVersion != nil && [self.challenge.identityProviderTiqrProtocolVersion intValue] >= 1) {
        // Parse the JSON result
        NSArray *result = [[JSONDecoder decoder] objectWithData:self.data];
        self.data = nil;
        
        NSNumber *responseCode = [NSNumber numberWithInt:[[result valueForKey:@"responseCode"] intValue]];
        if ([responseCode intValue] == EnrollmentChallengeResponseCodeSuccess || [responseCode intValue] == EnrollmentChallengeResponseCodeSuccessUsernameByServer) {
            [self.delegate enrollmentConfirmationRequestDidFinish:self];
        } else {
            NSString *title = NSLocalizedString(@"enroll_error_title", @"Enrollment error title");
            NSString *message = nil;
            NSString *serverMessage = [result valueForKey:@"message"];
            if (serverMessage) {
                message = serverMessage;
            } else if ([responseCode intValue] == EnrollmentChallengeResponseCodeVerificationRequired) {
                message = NSLocalizedString(@"enroll_error_verification_needed", @"Account created, verification required error message");
            } else if ([responseCode intValue] == EnrollmentChallengeResponseCodeFailureUsernameTaken) {
                message = NSLocalizedString(@"enroll_error_username_taken", @"Enrollment username exists");
            } else if ([responseCode intValue] == EnrollmentChallengeResponseCodeFailure) {
                message = NSLocalizedString(@"unknown_enroll_error_message", @"Unknown error message");
            } else {
                message = NSLocalizedString(@"unknown_enroll_error_message", @"Unknown error message");
            }
            
            NSMutableDictionary *details = [NSMutableDictionary dictionary];
            [details setValue:title forKey:NSLocalizedDescriptionKey];
            [details setValue:message forKey:NSLocalizedFailureReasonErrorKey];
            
            NSError *error = [NSError errorWithDomain:TIQRECRErrorDomain code:TIQRECRUnknownError userInfo:details];
            [self.delegate enrollmentConfirmationRequest:self didFailWithError:error];
        }
    } else {
        // Parse string result
        NSString *response = [[NSString alloc] initWithBytes:[self.data bytes] length:[self.data length] encoding:NSUTF8StringEncoding];
        self.data = nil;
        if ([response isEqualToString:@"OK"]) {
            [self.delegate enrollmentConfirmationRequestDidFinish:self];
        } else {
            // TODO: server should return different error codes
            NSString *title = NSLocalizedString(@"unknown_error", @"Unknown error title");
            NSString *message = NSLocalizedString(@"unknown_enroll_error_message", @"Unknown error message");
            
            NSMutableDictionary *details = [NSMutableDictionary dictionary];
            [details setValue:title forKey:NSLocalizedDescriptionKey];
            [details setValue:message forKey:NSLocalizedFailureReasonErrorKey];
            
            NSError *error = [NSError errorWithDomain:TIQRECRErrorDomain code:TIQRECRUnknownError userInfo:details];
            [self.delegate enrollmentConfirmationRequest:self didFailWithError:error];
        }
        
        [response release];
    }
    
    [connection release];
}

@end
