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

#import "EnrollmentPINVerificationViewController.h"
#import "EnrollmentSummaryViewController.h"
#import "EnrollmentConfirmationRequest.h"
#import "IdentityProvider.h"
#import "Identity+Utils.h"
#import "SecretStore.h"
#import "ErrorViewController.h"
#import "MBProgressHUD.h"

@interface EnrollmentPINVerificationViewController ()

@property (nonatomic, retain) EnrollmentChallenge *challenge;
@property (nonatomic, copy) NSString *PIN;
@property (nonatomic, retain) NSData *responseData;

@end

@implementation EnrollmentPINVerificationViewController

@synthesize managedObjectContext=managedObjectContext_;
@synthesize challenge=challenge_;
@synthesize PIN=PIN_;
@synthesize responseData=responseData_;

- (id)initWithEnrollmentChallenge:(EnrollmentChallenge *)challenge PIN:(NSString *)PIN {
    self = [super init];
    if (self != nil) {
        self.challenge = challenge;
        self.PIN = PIN;
        self.delegate = self;        
    }
	
	return self;
}

- (void)viewDidLoad {
    [super viewDidLoad];

    self.title = NSLocalizedString(@"enrollment_confirmation_header_title", @"Account activation title");
    self.navigationItem.backBarButtonItem = [[[UIBarButtonItem alloc] initWithTitle:NSLocalizedString(@"password_verify_back_button", @"Enrollment PIN verification back button title") style:UIBarButtonItemStyleBordered target:nil action:nil] autorelease];        
    self.subtitle = NSLocalizedString(@"login_verify_intro", @"Enrollment PIN verification title");
    self.description = NSLocalizedString(@"login_verify_message", @"Enter your PIN code again for verification. Please note the animal icon. This will help you remember your PIN code."); 
}

- (BOOL)storeProviderAndIdentity {
	NSManagedObjectContext *context = self.managedObjectContext;
	
	IdentityProvider *identityProvider = self.challenge.identityProvider;
	if (identityProvider == nil) {
		identityProvider = [NSEntityDescription insertNewObjectForEntityForName:@"IdentityProvider" inManagedObjectContext:context];	
		identityProvider.identifier = self.challenge.identityProviderIdentifier;
		identityProvider.displayName = self.challenge.identityProviderDisplayName;
		identityProvider.authenticationUrl = self.challenge.identityProviderAuthenticationUrl;
        identityProvider.infoUrl = self.challenge.identityProviderInfoUrl;
        identityProvider.ocraSuite = self.challenge.identityProviderOcraSuite;
		identityProvider.logo = self.challenge.identityProviderLogo;
	}
	
	Identity *identity = self.challenge.identity;
    if (identity == nil) {
        identity = [NSEntityDescription insertNewObjectForEntityForName:@"Identity" inManagedObjectContext:context];	
        identity.identifier = self.challenge.identityIdentifier;
        identity.sortIndex = [NSNumber numberWithInt:[Identity maxSortIndexInManagedObjectContext:context] + 1];		
        identity.identityProvider = identityProvider;
    }
    
	identity.displayName = self.challenge.identityDisplayName;
	
	NSError *error = nil;
	if ([context save:&error]) {
        self.challenge.identity = identity;
        self.challenge.identityProvider = identityProvider;
        return YES;
	} else {
		[context rollback];
		return NO;			
    }
}

- (void)deleteIdentity {
    if (![self.challenge.identity.blocked boolValue]) {
        [self.managedObjectContext deleteObject:self.challenge.identity];
        [self.managedObjectContext save:nil];
    }
}

- (BOOL)storeSecret {
    SecretStore *store = [SecretStore secretStoreForIdentity:self.challenge.identityIdentifier identityProvider:self.challenge.identityProviderIdentifier];	
    [store setSecret:self.challenge.identitySecret PIN:self.challenge.identityPIN];
    return [store storeInKeychain];
}

- (void)deleteSecret {
    SecretStore *store = [SecretStore secretStoreForIdentity:self.challenge.identityIdentifier identityProvider:self.challenge.identityProviderIdentifier];	
    [store deleteFromKeychain];
}

- (void)enrollmentConfirmationRequestDidFinish:(EnrollmentConfirmationRequest *)request {
	[MBProgressHUD hideHUDForView:self.navigationController.view animated:YES];    
    [request release];
    
    self.challenge.identity.blocked = [NSNumber numberWithBool:NO];
    [self.managedObjectContext save:nil];
    
    EnrollmentSummaryViewController *viewController = [[EnrollmentSummaryViewController alloc] initWithEnrollmentChallenge:self.challenge];
    viewController.managedObjectContext = self.managedObjectContext;
    [self.navigationController pushViewController:viewController animated:YES];
    [viewController release];    
}

- (void)enrollmentConfirmationRequest:(EnrollmentConfirmationRequest *)request didFailWithError:(NSError *)error {
	[MBProgressHUD hideHUDForView:self.navigationController.view animated:YES];    
    [request release];
    [self deleteIdentity];
    [self deleteSecret];

    UIViewController *viewController = [[ErrorViewController alloc] initWithTitle:self.title errorTitle:[error localizedDescription] errorMessage:[error localizedFailureReason]];
    [self.navigationController pushViewController:viewController animated:YES];
    [viewController release];
}

- (void)PINViewController:(PINViewController *)viewController didFinishWithPIN:(NSString *)PIN {
    if (![PIN isEqualToString:self.PIN]) {
        [self clear];
        NSString *errorTitle = NSLocalizedString(@"passwords_dont_match_title", @"Error title if PIN's don't match");
        NSString *errorMessage = NSLocalizedString(@"passwords_dont_match", @"Error message if PINs don't match");
        [self showErrorWithTitle:errorTitle message:errorMessage];
        return;
    }
    
	self.challenge.identitySecret = [SecretStore generateSecret];
	self.challenge.identityPIN = PIN;
    
    if (![self storeProviderAndIdentity]) {
        NSString *errorTitle = NSLocalizedString(@"error_enroll_failed_to_store_identity_title", @"Account cannot be saved title");
        NSString *errorMessage = NSLocalizedString(@"error_enroll_failed_to_store_identity", @"Account cannot be saved message");
        UIViewController *viewController = [[ErrorViewController alloc] initWithTitle:self.title errorTitle:errorTitle errorMessage:errorMessage];
        [self.navigationController pushViewController:viewController animated:YES];
        [viewController release];
        return;
    }
    
    if (![self storeSecret]) {
        NSString *errorTitle = NSLocalizedString(@"error_enroll_failed_to_store_identity_title", @"Account cannot be saved title");
        NSString *errorMessage = NSLocalizedString(@"error_enroll_failed_to_generate_secret", @"Failed to generate identity secret. Please contact support.");
        UIViewController *viewController = [[ErrorViewController alloc] initWithTitle:self.title errorTitle:errorTitle errorMessage:errorMessage];
        [self.navigationController pushViewController:viewController animated:YES];
        [viewController release];
        return;
    }
    
	[MBProgressHUD showHUDAddedTo:self.navigationController.view animated:YES];        
    EnrollmentConfirmationRequest *request = [[EnrollmentConfirmationRequest alloc] initWithEnrollmentChallenge:self.challenge];
    request.delegate = self;
    [request send];
}

- (void)dealloc {
    self.challenge = nil;
    self.PIN = nil;    
    self.managedObjectContext = nil;
    [super dealloc];
}

@end
