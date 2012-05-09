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
    
    NSString *title = NSLocalizedString(@"No connection", @"No connection error title");
    NSString *message = NSLocalizedString(@"You appear to have no active Internet connection.", @"No connection error message");
    NSMutableDictionary *details = [NSMutableDictionary dictionary];
    [details setValue:title forKey:NSLocalizedDescriptionKey];
    [details setValue:message forKey:NSLocalizedFailureReasonErrorKey];    
    [details setValue:connectionError forKey:NSUnderlyingErrorKey];
    
    NSError *error = [NSError errorWithDomain:TIQRACRErrorDomain code:TIQRACRConnectionError userInfo:details];
    [self.delegate authenticationConfirmationRequest:self didFailWithError:error];    
}

- (void)connectionDidFinishLoading:(NSURLConnection *)connection {
	NSString *response = [[NSString alloc] initWithBytes:[self.data bytes] length:[self.data length] encoding:NSUTF8StringEncoding];
    self.data = nil;
    
	if ([response isEqualToString:@"OK"]) {
		[self.delegate authenticationConfirmationRequestDidFinish:self];
	} else {
        NSInteger code = TIQRACRUnknownError;
        NSString *title = NSLocalizedString(@"Unknown error", @"Unknown error title");
        NSString *message = NSLocalizedString(@"An unknown error occurred. Please contact support.", @"Unknown error message");
        NSNumber *attemptsLeft = nil;

        if ([response isEqualToString:@"ACCOUNT_BLOCKED"]) {
            code = TIQRACRAccountBlockedError;
            title = NSLocalizedString(@"Account blocked", @"INVALID_RESPONSE error title (0 attempts left)");
            message = NSLocalizedString(@"This account can no longer be used.", @"INVALID_RESPONSE error message (0 attempts left)");            
        } else if ([response isEqualToString:@"INVALID_CHALLENGE"]) {
            code = TIQRACRInvalidChallengeError;
            title = NSLocalizedString(@"Invalid challenge", @"INVALID_CHALLENGE error title");
            message = NSLocalizedString(@"The scanned QR tag is either invalid or expired. Please try again.", @"INVALID_CHALLENGE error message");
        } else if ([response isEqualToString:@"INVALID_REQUEST"]) {
            code = TIQRACRInvalidRequestError;  
            title = NSLocalizedString(@"Invalid request", @"INVALID_REQUEST error title");            
            message = NSLocalizedString(@"The server doesn't recognize the login request. Please contact support.", @"INVALID_REQUEST error message");
        } else if ([response length]>=17 && [[response substringToIndex:17] isEqualToString:@"INVALID_RESPONSE:"]) {
            attemptsLeft = [NSNumber numberWithInt:[[response substringFromIndex:17] intValue]];
            code = TIQRACRInvalidResponseError;            
            if ([attemptsLeft intValue] > 1) {
                title = NSLocalizedString(@"Wrong PIN", @"INVALID_RESPONSE error title (> 1 attempts left)");
                message = NSLocalizedString(@"You supplied an incorrect PIN. You have %d attempts left. Please enter your PIN again. Check the verification icon for the last digit.", @"INVALID_RESPONSE error message (> 1 attempts left)");            
                message = [NSString stringWithFormat:message, [attemptsLeft intValue]];
            } else if ([attemptsLeft intValue] == 1) {
                title = NSLocalizedString(@"Wrong PIN", @"INVALID_RESPONSE error title (1 attempt left)");
                message = NSLocalizedString(@"You have one last attempt left. If you enter an incorrect PIN again all the accounts on this mobile phone will be blocked.", @"INVALID_RESPONSE error message (1 attempt left)");            
                message = [NSString stringWithFormat:message];
            } else {
                title = NSLocalizedString(@"Account blocked", @"INVALID_RESPONSE error title (0 attempts left)");
                message = NSLocalizedString(@"This account can no longer be used.", @"INVALID_RESPONSE error message (0 attempts left)");            
            }
        } else if ([response isEqualToString:@"INVALID_USERID"]) {
            code = TIQRACRInvalidUserError;   
            title = NSLocalizedString(@"Invalid account", @"INVALID_USERID error title");
            message = NSLocalizedString(@"You tried to login with an invalid or unknown account. Please (re-)activate your account first.", @"INVALID_USERID error message");            
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
    
	[response release];	
    [connection release];
}

- (void)send {
	NSString *escapedSessionKey = [self.challenge.sessionKey stringByAddingPercentEscapesUsingEncoding:NSUTF8StringEncoding];
	NSString *escapedUserId = [self.challenge.identity.identifier stringByAddingPercentEscapesUsingEncoding:NSUTF8StringEncoding];
	NSString *escapedResponse = [self.response stringByAddingPercentEscapesUsingEncoding:NSUTF8StringEncoding];
	NSString *escapedLanguage = [[NSLocale preferredLanguages] objectAtIndex:0];
	NSString *notificationToken = [NotificationRegistration sharedInstance].notificationToken;
	NSString *escapedNotificationToken = [notificationToken stringByAddingPercentEscapesUsingEncoding:NSUTF8StringEncoding];
	NSString *body = [NSString stringWithFormat:@"sessionKey=%@&userId=%@&response=%@&language=%@&notificationType=APNS&notificationAddress=%@", escapedSessionKey, escapedUserId, escapedResponse, escapedLanguage, escapedNotificationToken];
        
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
