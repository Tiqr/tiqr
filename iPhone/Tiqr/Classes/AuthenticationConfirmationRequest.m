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

#import "AuthenticationConfirmationRequest.h"
#import "NotificationRegistration.h"
#import "JSONKit.h"


NSString *const TIQRACRErrorDomain = @"org.tiqr.acr";
NSString *const TIQRACRAttemptsLeftErrorKey = @"AttempsLeftErrorKey";  

@interface AuthenticationConfirmationRequest ()

@property (nonatomic, retain) AuthenticationChallenge *challenge;
@property (nonatomic, copy) NSString *response;
@property (nonatomic, retain) NSMutableData *data;

@end

@implementation AuthenticationConfirmationRequest

@synthesize delegate=delegate_;
@synthesize challenge=challenge_;
@synthesize response=response_;
@synthesize data=data_;

- (id)initWithAuthenticationChallenge:(AuthenticationChallenge *)challenge response:(NSString *)response {
    self = [super init];
    if (self != nil) {
        self.challenge = challenge;
        self.response = response;
    }
    
    return self;
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
    NSString *message = NSLocalizedString(@"no_active_internet_connection.", @"You appear to have no active Internet connection.");
    NSMutableDictionary *details = [NSMutableDictionary dictionary];
    [details setValue:title forKey:NSLocalizedDescriptionKey];
    [details setValue:message forKey:NSLocalizedFailureReasonErrorKey];    
    [details setValue:connectionError forKey:NSUnderlyingErrorKey];
    
    NSError *error = [NSError errorWithDomain:TIQRACRErrorDomain code:TIQRACRConnectionError userInfo:details];
    [self.delegate authenticationConfirmationRequest:self didFailWithError:error];    
}

- (void)connectionDidFinishLoading:(NSURLConnection *)connection {
    NSArray *result = [[JSONDecoder decoder] objectWithData:self.data];
    self.data = nil;

    NSNumber *responseCode = [NSNumber numberWithInt:[[result valueForKey:@"responseCode"] intValue]];
	if ([responseCode intValue] == AuthenticationChallengeResponseCodeSuccess) {
		[self.delegate authenticationConfirmationRequestDidFinish:self];
	} else {
        NSInteger code = TIQRACRUnknownError;
        NSString *title = NSLocalizedString(@"unknown_error", @"Unknown error title");
        NSString *message = NSLocalizedString(@"error_auth_unknown_error", @"Unknown error message");
        NSNumber *attemptsLeft = nil;
        if ([responseCode intValue] == AuthenticationChallengeResponseCodeAccountBlocked) {
            code = TIQRACRAccountBlockedError;
            title = NSLocalizedString(@"error_auth_account_blocked_title", @"INVALID_RESPONSE error title (0 attempts left)");
            message = NSLocalizedString(@"error_auth_account_blocked_message", @"INVALID_RESPONSE error message (0 attempts left)");            
        } else if ([responseCode intValue] == AuthenticationChallengeResponseCodeInvalidChallenge) {
            code = TIQRACRInvalidChallengeError;
            title = NSLocalizedString(@"error_auth_invalid_challenge_title", @"INVALID_CHALLENGE error title");
            message = NSLocalizedString(@"error_auth_invalid_challenge_message", @"INVALID_CHALLENGE error message");
        } else if ([responseCode intValue] == AuthenticationChallengeResponseCodeInvalidRequest) {
            code = TIQRACRInvalidRequestError;  
            title = NSLocalizedString(@"error_auth_invalid_request_title", @"INVALID_REQUEST error title");            
            message = NSLocalizedString(@"error_auth_invalid_request_message", @"INVALID_REQUEST error message");
        } else if ([responseCode intValue] == AuthenticationChallengeResponseCodeInvalidUsernamePasswordPin) {
            attemptsLeft = [NSNumber numberWithInt:[[result valueForKey:@"attemptsLeft"] intValue]];
            if ([attemptsLeft intValue] > 1) {
                title = NSLocalizedString(@"error_auth_wrong_pin", @"INVALID_RESPONSE error title (> 1 attempts left)");
                message = NSLocalizedString(@"error_auth_x_attempts_left", @"INVALID_RESPONSE error message (> 1 attempts left)");            
                message = [NSString stringWithFormat:message, [attemptsLeft intValue]];                
            } else if ([attemptsLeft intValue] == 1) {
                title = NSLocalizedString(@"error_auth_wrong_pin", @"INVALID_RESPONSE error title (1 attempt left)");
                message = NSLocalizedString(@"error_auth_one_attempt_left", @"INVALID_RESPONSE error message (1 attempt left)");            
            } else {
                title = NSLocalizedString(@"error_auth_account_blocked_title", @"INVALID_RESPONSE error title (0 attempts left)");
                message = NSLocalizedString(@"error_auth_account_blocked_message", @"INVALID_RESPONSE error message (0 attempts left)");            
            }
        }
        
        NSString *serverMessage = [result valueForKey:@"message"];
        if (serverMessage) {
            message = serverMessage;
        }
        
        NSMutableDictionary *details = [NSMutableDictionary dictionary];
        [details setValue:title forKey:NSLocalizedDescriptionKey];
        [details setValue:message forKey:NSLocalizedFailureReasonErrorKey];
        if (attemptsLeft != nil) {
            [details setValue:attemptsLeft forKey:TIQRACRAttemptsLeftErrorKey];
        }
        
        NSError *error = [NSError errorWithDomain:TIQRACRErrorDomain code:code userInfo:details];
        [self.delegate authenticationConfirmationRequest:self didFailWithError:error];
	}
    
	[result release];	
    [connection release];
}

- (void)send {
	NSString *escapedSessionKey = [self.challenge.sessionKey stringByAddingPercentEscapesUsingEncoding:NSUTF8StringEncoding];
	NSString *escapedUserId = [self.challenge.identity.identifier stringByAddingPercentEscapesUsingEncoding:NSUTF8StringEncoding];
	NSString *escapedResponse = [self.response stringByAddingPercentEscapesUsingEncoding:NSUTF8StringEncoding];
	NSString *escapedLanguage = [[NSLocale preferredLanguages] objectAtIndex:0];
	NSString *notificationToken = [NotificationRegistration sharedInstance].notificationToken;
	NSString *escapedNotificationToken = [notificationToken stringByAddingPercentEscapesUsingEncoding:NSUTF8StringEncoding];
    NSString *operation = @"login";
    NSString *version = [[NSBundle mainBundle] objectForInfoDictionaryKey:@"AnimateLoginProtocolVersion"];
	NSString *body = [NSString stringWithFormat:@"sessionKey=%@&userId=%@&response=%@&language=%@&notificationType=APNS&notificationAddress=%@&operation=%@&version=%@", escapedSessionKey, escapedUserId, escapedResponse, escapedLanguage, escapedNotificationToken, operation, version];
        
	NSMutableURLRequest *request = [[NSMutableURLRequest alloc] initWithURL:[NSURL URLWithString:self.challenge.identityProvider.authenticationUrl]];
	[request setCachePolicy:NSURLRequestReloadIgnoringCacheData];
	[request setTimeoutInterval:5.0];
	[request setHTTPMethod:@"POST"];
	[request setHTTPBody:[body dataUsingEncoding:NSUTF8StringEncoding]];
    
    self.data = [NSMutableData data];
	[[NSURLConnection alloc] initWithRequest:request delegate:self];
}

- (void)dealloc {
    self.challenge = nil;
    self.response = nil;
    self.data = nil;
    [super dealloc];
}

@end
