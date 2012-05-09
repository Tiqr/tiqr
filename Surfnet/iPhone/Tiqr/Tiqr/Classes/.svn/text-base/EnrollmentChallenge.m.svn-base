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

#import "Challenge-Protected.h"
#import "EnrollmentChallenge.h"
#import "EnrollmentChallenge-Protected.h"
#import "JSONKit.h"
#import "Identity+Utils.h"
#import "IdentityProvider+Utils.h"

NSString *const TIQRECErrorDomain = @"org.tiqr.ec";

@interface EnrollmentChallenge ()

@property (nonatomic, assign) BOOL allowFiles;

@end

@implementation EnrollmentChallenge

@synthesize identityProviderIdentifier=identityProviderIdentifier_, identityProviderDisplayName=identityProviderDisplayName_, identityProviderAuthenticationUrl=identityProviderAuthenticationUrl_, identityProviderInfoUrl=indentityProviderInfoUrl_;
@synthesize identityProviderOcraSuite=identityProviderOcraSuite_, identityProviderLogo=identityProviderLogo_, identityProvider=identityProvider_;
@synthesize identityIdentifier=identityIdentifier_, identityDisplayName=identityDisplayName_, identitySecret=identitySecret_, identityPIN=identityPIN_, identity=identity_;
@synthesize enrollmentUrl=enrollmentUrl_;
@synthesize returnUrl=returnUrl_;
@synthesize allowFiles=allowFiles_;

- (id)initWithRawChallenge:(NSString *)challenge managedObjectContext:(NSManagedObjectContext *)context allowFiles:(BOOL)allowFiles {
    self = [super initWithRawChallenge:challenge managedObjectContext:context autoParse:NO];
    if (self != nil) {
        self.allowFiles = allowFiles;
		[self parseRawChallenge];
	}
	
	return self;
}

- (id)initWithRawChallenge:(NSString *)challenge managedObjectContext:(NSManagedObjectContext *)context {
    return [self initWithRawChallenge:challenge managedObjectContext:context allowFiles:NO];
}

- (BOOL)isValidMetadata:(NSDictionary *)metadata {
    // TODO: service => identityProvider 
	if ([metadata valueForKey:@"service"] == nil ||
		[metadata valueForKey:@"identity"] == nil) {
		return NO;
	}

	// TODO: improve validation
    
	return YES;
}

- (NSData *)downloadSynchronously:(NSURL *)url error:(NSError **)error {
	NSURLResponse *response = nil;
	NSURLRequest *request = [NSURLRequest requestWithURL:url];
	NSData *data = [NSURLConnection sendSynchronousRequest:request returningResponse:&response error:error];
	return data;
}

- (BOOL)assignIdentityProviderMetadata:(NSDictionary *)metadata {
	self.identityProviderIdentifier = [[metadata objectForKey:@"identifier"] description];
	self.identityProvider = [IdentityProvider findIdentityProviderWithIdentifier:self.identityProviderIdentifier inManagedObjectContext:self.managedObjectContext];
	
	if (self.identityProvider != nil) {
		self.identityProviderDisplayName = self.identityProvider.displayName;
		self.identityProviderAuthenticationUrl = self.identityProvider.authenticationUrl;	
        self.identityProviderOcraSuite = self.identityProvider.ocraSuite;
		self.identityProviderLogo = self.identityProvider.logo;
	} else {
		NSURL *logoUrl = [NSURL URLWithString:[[metadata objectForKey:@"logoUrl"] description]];		
		NSError *error = nil;		
		NSData *logo = [self downloadSynchronously:logoUrl error:&error];
		if (error != nil) {
            NSString *errorTitle = NSLocalizedString(@"No identity provider logo", @"No identity provider logo title");
            NSString *errorMessage = NSLocalizedString(@"Cannot retrieve the identity provider logo. Please contact the identity provider and try again later.", @"No identity provider logo message");
            NSDictionary *details = [NSDictionary dictionaryWithObjectsAndKeys:errorTitle, NSLocalizedDescriptionKey, errorMessage, NSLocalizedFailureReasonErrorKey, error, NSUnderlyingErrorKey, nil];
            self.error = [NSError errorWithDomain:TIQRECErrorDomain code:TIQRECIdentityProviderLogoError userInfo:details];        
			return NO;
		}
		
		self.identityProviderDisplayName =  [[metadata objectForKey:@"displayName"] description];
		self.identityProviderAuthenticationUrl = [[metadata objectForKey:@"authenticationUrl"] description];	
		self.identityProviderInfoUrl = [[metadata objectForKey:@"infoUrl"] description];        
        self.identityProviderOcraSuite = [[metadata objectForKey:@"ocraSuite"] description];
		self.identityProviderLogo = logo;
	}	
	
	return YES;
}

- (BOOL)assignIdentityMetadata:(NSDictionary *)metadata {
	self.identityIdentifier = [[metadata objectForKey:@"identifier"] description];
	self.identityDisplayName = [[metadata objectForKey:@"displayName"] description];
	self.identitySecret = nil;
	
	if (self.identityProvider != nil) {
		Identity *identity = [Identity findIdentityWithIdentifier:self.identityIdentifier forIdentityProvider:self.identityProvider inManagedObjectContext:self.managedObjectContext];
		if (identity != nil && [identity.blocked boolValue]) {
            self.identity = identity;
        } else if (identity != nil) {
            NSString *errorTitle = NSLocalizedString(@"Account already activated", @"Account already activated title");
            NSString *errorMessage = NSLocalizedString(@"This account has already been activated and is ready to be used.", @"Account already activated message");
            NSDictionary *details = [NSDictionary dictionaryWithObjectsAndKeys:errorTitle, NSLocalizedDescriptionKey, errorMessage, NSLocalizedFailureReasonErrorKey, nil];
            self.error = [NSError errorWithDomain:TIQRECErrorDomain code:TIQRECAccountAlreadyExistsError userInfo:details];        
			return NO;			
		}
	}
								 
	return YES;
}

- (void)parseRawChallenge {
    NSString *scheme = [[NSBundle mainBundle] objectForInfoDictionaryKey:@"TIQREnrollmentURLScheme"]; 
    NSURL *fullURL = [NSURL URLWithString:self.rawChallenge];
    if (fullURL == nil || ![fullURL.scheme isEqualToString:scheme]) {
        NSString *errorTitle = NSLocalizedString(@"Invalid QR tag", @"Invalid QR tag title");
        NSString *errorMessage = NSLocalizedString(@"Unable to interpret the scanned QR tag. Please try again. If the problem persists, please contact the website administrator.", @"Invalid QR tag message");
        NSDictionary *details = [NSDictionary dictionaryWithObjectsAndKeys:errorTitle, NSLocalizedDescriptionKey, errorMessage, NSLocalizedFailureReasonErrorKey, nil];
        self.error = [NSError errorWithDomain:TIQRECErrorDomain code:TIQRECInvalidQRTagError userInfo:details];        
		return;        
    }
    
	NSURL *url = [NSURL URLWithString:[self.rawChallenge substringFromIndex:13]];
    if (url == nil) {
        NSString *errorTitle = NSLocalizedString(@"Invalid QR tag", @"Invalid QR tag title");
        NSString *errorMessage = NSLocalizedString(@"Unable to interpret the scanned QR tag. Please try again. If the problem persists, please contact the website administrator.", @"Invalid QR tag message");
        NSDictionary *details = [NSDictionary dictionaryWithObjectsAndKeys:errorTitle, NSLocalizedDescriptionKey, errorMessage, NSLocalizedFailureReasonErrorKey, nil];
        self.error = [NSError errorWithDomain:TIQRECErrorDomain code:TIQRECInvalidQRTagError userInfo:details];        
		return;        
    }
    
	if (![url.scheme isEqualToString:@"http"] && ![url.scheme isEqualToString:@"https"] && ![url.scheme isEqualToString:@"file"]) {
        NSString *errorTitle = NSLocalizedString(@"Invalid QR tag", @"Invalid QR tag title");
        NSString *errorMessage = NSLocalizedString(@"Unable to interpret the scanned QR tag. Please try again. If the problem persists, please contact the website administrator.", @"Invalid QR tag message");
        NSDictionary *details = [NSDictionary dictionaryWithObjectsAndKeys:errorTitle, NSLocalizedDescriptionKey, errorMessage, NSLocalizedFailureReasonErrorKey, nil];
        self.error = [NSError errorWithDomain:TIQRECErrorDomain code:TIQRECInvalidQRTagError userInfo:details];        
		return;
	} else if ([url.scheme isEqualToString:@"file"] && !self.allowFiles) {
        NSString *errorTitle = NSLocalizedString(@"Invalid QR tag", @"Invalid QR tag title");
        NSString *errorMessage = NSLocalizedString(@"Unable to interpret the scanned QR tag. Please try again. If the problem persists, please contact the website administrator.", @"Invalid QR tag message");
        NSDictionary *details = [NSDictionary dictionaryWithObjectsAndKeys:errorTitle, NSLocalizedDescriptionKey, errorMessage, NSLocalizedFailureReasonErrorKey, nil];
        self.error = [NSError errorWithDomain:TIQRECErrorDomain code:TIQRECInvalidQRTagError userInfo:details];        
		return;
	}
    
    
	NSError *error = nil;
	NSData *data = [self downloadSynchronously:url error:&error];
	if (error != nil) {
        NSString *errorTitle = NSLocalizedString(@"No connection", @"No connection title");
        NSString *errorMessage = NSLocalizedString(@"You need an Internet connection to activate your account. Please try again later.", @"No connection message");
        NSDictionary *details = [NSDictionary dictionaryWithObjectsAndKeys:errorTitle, NSLocalizedDescriptionKey, errorMessage, NSLocalizedFailureReasonErrorKey, error, NSUnderlyingErrorKey, nil];
        self.error = [NSError errorWithDomain:TIQRECErrorDomain code:TIQRECConnectionError userInfo:details];        
		return;
	}

	NSDictionary *metadata = nil;
	
	@try {
        id object = [data objectFromJSONData];
        if ([object isKindOfClass:[NSDictionary class]]) {
            metadata = object;
        }
	} @catch (NSException *exception) {
        metadata = nil;
    } 

	if (metadata == nil || error != nil || ![self isValidMetadata:metadata]) {
        NSString *errorTitle = NSLocalizedString(@"Invalid response", @"Invalid response title");
        NSString *errorMessage = NSLocalizedString(@"The identity provider returned an invalid response. Please contact the identity provider and try again later.", @"Invalid response message");
        NSDictionary *details = [NSDictionary dictionaryWithObjectsAndKeys:errorTitle, NSLocalizedDescriptionKey, errorMessage, NSLocalizedFailureReasonErrorKey, error, NSUnderlyingErrorKey, nil];
        self.error = [NSError errorWithDomain:TIQRECErrorDomain code:TIQRECInvalidResponseError userInfo:details];        
		return;        
	}
	
	NSMutableDictionary *identityProviderMetadata = [NSMutableDictionary dictionaryWithDictionary:[metadata objectForKey:@"service"]];
	if (![self assignIdentityProviderMetadata:identityProviderMetadata]) {
		return;
	}

	NSDictionary *identityMetadata = [metadata objectForKey:@"identity"];	
	if (![self assignIdentityMetadata:identityMetadata]) {
		return;
	}
    
    NSString *regex = @"^http(s)?://.*";
    NSPredicate *protocolPredicate = [NSPredicate predicateWithFormat:@"SELF MATCHES %@", regex];
    
    if (url.query != nil && [url.query length] > 0 && [protocolPredicate evaluateWithObject:url.query] == YES) {
        self.returnUrl = [self decodeURL:url.query];
    } else {
        self.returnUrl = nil;
    }
	
	self.returnUrl = nil; // TODO: support return URL url.query == nil || [url.query length] == 0 ? nil : url.query;	
	self.enrollmentUrl = [[identityProviderMetadata objectForKey:@"enrollmentUrl"] description];
}

- (void)dealloc {
    self.identityProviderIdentifier = nil;
    self.identityProviderDisplayName = nil;
    self.identityProviderAuthenticationUrl = nil;
    self.identityProviderInfoUrl = nil;
    self.identityProviderOcraSuite = nil;
    self.identityProviderLogo = nil;
    self.identityProvider = nil;
    self.identityIdentifier = nil;
    self.identityDisplayName = nil;
    self.identitySecret = nil;
    self.identityPIN = nil;
    self.identity = nil;
    self.enrollmentUrl = nil;
    self.returnUrl = nil;
    [super dealloc];
}

@end