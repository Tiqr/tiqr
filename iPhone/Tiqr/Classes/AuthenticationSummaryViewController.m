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

#import "AuthenticationSummaryViewController.h"
#import "AuthenticationSummaryViewController-Protected.h"
#import "FooterController.h"
#import "TiqrAppDelegate.h"

@interface AuthenticationSummaryViewController ()

@property (nonatomic, retain) AuthenticationChallenge *challenge;
@property (nonatomic, retain) FooterController *footerController;

@property (nonatomic, retain) IBOutlet UILabel *loginConfirmLabel;
@property (nonatomic, retain) IBOutlet UILabel *loginInformationLabel;
@property (nonatomic, retain) IBOutlet UILabel *toLabel;

@end

@implementation AuthenticationSummaryViewController

@synthesize challenge=challenge_;
@synthesize footerController=footerController_;
@synthesize managedObjectContext=managedObjectContext_;
@synthesize identityProviderLogoImageView=identityProviderLogoImageView_, identityDisplayNameLabel=identityDisplayNameLabel_, identityProviderDisplayNameLabel=identityProviderDisplayNameLabel_, serviceProviderDisplayNameLabel=serviceProviderDisplayNameLabel_, serviceProviderIdentifierLabel=serviceProviderIdentifierLabel_;
@synthesize returnButton=returnButton_;

@synthesize loginConfirmLabel=loginConfirmLabel_;
@synthesize loginInformationLabel=loginInformationLabel_;
@synthesize toLabel=toLabel_;

- (id)initWithAuthenticationChallenge:(AuthenticationChallenge *)challenge {
    self = [super initWithNibName:@"AuthenticationSummaryView" bundle:nil];
	if (self != nil) {
		self.challenge = challenge;
        self.footerController = [[[FooterController alloc] init] autorelease];        
	}
	
	return self;
}

- (void)viewDidLoad {
    [super viewDidLoad];
    
    self.loginConfirmLabel.text = NSLocalizedString(@"successfully_logged_in", @"Login succes confirmation message");
    self.loginInformationLabel.text = NSLocalizedString(@"loggedin_with_account", @"Login information message");
    self.toLabel.text = NSLocalizedString(@"to_service_provider", @"to:");
    
    self.title = NSLocalizedString(@"authentication_title", @"Login title");
    UIBarButtonItem *backButton = [[[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemDone target:self action:@selector(done)] autorelease];
    self.navigationItem.leftBarButtonItem = backButton;
    
	self.identityProviderLogoImageView.image = [[UIImage alloc] initWithData:self.challenge.identityProvider.logo];
	self.identityDisplayNameLabel.text = self.challenge.identity.displayName;
	self.identityProviderDisplayNameLabel.text = self.challenge.identityProvider.displayName;	
	self.serviceProviderDisplayNameLabel.text = self.challenge.serviceProviderDisplayName;
	self.serviceProviderIdentifierLabel.text = self.challenge.serviceProviderIdentifier;
    
    if (self.challenge.returnUrl != nil) {
        [self.returnButton setTitle:NSLocalizedString(@"return_button", @"Return to button title") forState:UIControlStateNormal];
        self.returnButton.hidden = NO;
    }
    
    if ([self respondsToSelector:@selector(edgesForExtendedLayout)]) {
        self.edgesForExtendedLayout = UIRectEdgeNone;
    }
    
    [self.footerController addToView:self.view];
}

- (void)done {
    [(TiqrAppDelegate *)[UIApplication sharedApplication].delegate popToStartViewControllerAnimated:YES];
}

- (void)returnToCaller {
    [(TiqrAppDelegate *)[UIApplication sharedApplication].delegate popToStartViewControllerAnimated:NO];
    NSString *returnURL = [NSString stringWithFormat:@"%@?successful=1", self.challenge.returnUrl];
    [[UIApplication sharedApplication] openURL:[NSURL URLWithString:returnURL]];
}

- (void)resetOutlets {
	self.identityProviderLogoImageView = nil;
	self.identityDisplayNameLabel = nil;
	self.identityProviderDisplayNameLabel = nil;
	self.serviceProviderDisplayNameLabel = nil;
	self.serviceProviderIdentifierLabel = nil;
    self.returnButton = nil;
    self.loginConfirmLabel = nil;
    self.loginInformationLabel = nil;
    self.toLabel = nil;
}

- (void)viewDidUnload {
    [self resetOutlets];
    [super viewDidUnload];
}

- (void)dealloc {
    [self resetOutlets];
    
    self.challenge = nil;
    self.footerController = nil;
    self.managedObjectContext = nil;    
    
    [super dealloc];	
}

@end
