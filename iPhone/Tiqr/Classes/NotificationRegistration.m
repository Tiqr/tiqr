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

#import "NotificationRegistration.h"
#import "NSData+Hex.h"

static NotificationRegistration *sharedInstance = nil;

@implementation NotificationRegistration

#pragma mark -
#pragma mark Class instance methods

- (void)setNotificationToken:(NSString *)notificationToken {
	NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
	[defaults setValue:notificationToken forKey:@"SANotificationToken"];
	[defaults synchronize];
}

- (NSString *)notificationToken {
	NSUserDefaults *defaults = [NSUserDefaults standardUserDefaults];
	return [defaults stringForKey:@"SANotificationToken"];
}

- (void)sendRequestWithDeviceToken:(NSData *)deviceToken {
	NSString *escapedDeviceToken = [deviceToken hexStringValue];
	NSString *escapedLanguage = [[[NSLocale preferredLanguages] objectAtIndex:0] stringByAddingPercentEscapesUsingEncoding:NSUTF8StringEncoding];
	NSString *escapedNotificationToken = [self.notificationToken stringByAddingPercentEscapesUsingEncoding:NSUTF8StringEncoding];
	
	NSString *body;
	if (escapedNotificationToken == nil) {
	    body = [NSString stringWithFormat:@"deviceToken=%@&language=%@", escapedDeviceToken, escapedLanguage];	
	} else {
	    body = [NSString stringWithFormat:@"deviceToken=%@&notificationToken=%@&language=%@", escapedDeviceToken, escapedNotificationToken, escapedLanguage];				
	}

	NSString *url = [[NSBundle mainBundle] objectForInfoDictionaryKey:@"SANotificationRegistrationURL"];
	NSMutableURLRequest *request = [[NSMutableURLRequest alloc] initWithURL:[NSURL URLWithString:url]];
	[request setCachePolicy:NSURLRequestReloadIgnoringLocalAndRemoteCacheData];
	[request setTimeoutInterval:15.0];
	[request setHTTPMethod:@"POST"];
	[request setHTTPBody:[body dataUsingEncoding:NSUTF8StringEncoding]];

	NotificationRegistration *delegate = self;
	NSURLConnection *connection = [[NSURLConnection alloc] initWithRequest:request delegate:delegate];
	if (connection != nil && delegate != nil) {
		responseData = [[NSMutableData data] retain];
	}
}

- (void)connection:(NSURLConnection *)connection didReceiveResponse:(NSURLResponse *)response {
    [responseData setLength:0];
}

- (void)connection:(NSURLConnection *)connection didReceiveData:(NSData *)data {
    [responseData appendData:data];
}

- (void)connection:(NSURLConnection *)connection didFailWithError:(NSError *)error {
    [responseData release];
	responseData = nil;
}

- (void)connectionDidFinishLoading:(NSURLConnection *)connection {
	NSString *response = [[NSString alloc] initWithData:responseData encoding:NSUTF8StringEncoding];
	if ([response length] > 0) {
		[self setNotificationToken:response];
	}
	
	[response release];	
    [connection release];
    [responseData release];	
	responseData = nil;	
}

- (void)dealloc {
    [responseData release];		
	responseData = nil;
	[super dealloc];
}

#pragma mark -
#pragma mark Singleton methods

+ (NotificationRegistration *)sharedInstance {
    @synchronized(self) {
        if (sharedInstance == nil) {
			sharedInstance = [[NotificationRegistration alloc] init];
		}
	}
	
    return sharedInstance;
}

+ (id)allocWithZone:(NSZone *)zone {
    @synchronized(self) {
        if (sharedInstance == nil) {
            sharedInstance = [super allocWithZone:zone];
            return sharedInstance;  // assignment and return on first allocation
        }
    }
	
    return nil; // on subsequent allocation attempts return nil
}

- (id)copyWithZone:(NSZone *)zone {
    return self;
}

- (id)retain {
    return self;
}

- (unsigned)retainCount {
    return UINT_MAX;  // denotes an object that cannot be released
}

- (oneway void)release {
    //do nothing
}

- (id)autorelease {
    return self;
}

@end