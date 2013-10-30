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

#import "EnrollmentSummaryViewController.h"
#import "EnrollmentSummaryViewController-Protected.h"
#import "FooterController.h"
#import "TiqrAppDelegate.h"

@interface EnrollmentSummaryViewController ()

@property (nonatomic, retain) EnrollmentChallenge *challenge;
@property (nonatomic, retain) FooterController *footerController;
@property (nonatomic, retain) IBOutlet UILabel *accountActivatedLabel;
@property (nonatomic, retain) IBOutlet UILabel *accountReadyLabel;
@property (nonatomic, retain) IBOutlet UILabel *rememberPinLabel;

@end

@implementation EnrollmentSummaryViewController

@synthesize challenge=challenge_;
@synthesize footerController=footerController_;
@synthesize managedObjectContext=managedObjectContext_;
@synthesize identityProviderLogoImageView=identityProviderLogoImageView_, identityDisplayNameLabel=identityDisplayNameLabel_, identityProviderDisplayNameLabel=identityProviderDisplayNameLabel_;
@synthesize returnButton=returnButton_;
@synthesize accountReadyLabel=accountReadyLabel_,accountActivatedLabel=accountActivationLabel_,rememberPinLabel=rememberPinLabel_;

- (id)initWithEnrollmentChallenge:(EnrollmentChallenge *)challenge {
    self = [super initWithNibName:@"EnrollmentSummaryView" bundle:nil];
	if (self != nil) {
		self.challenge = challenge;
        self.footerController = [[[FooterController alloc] init] autorelease];
	}
	
	return self;
}

- (void)viewDidLoad {
    [super viewDidLoad];
    
    self.accountReadyLabel.text = NSLocalizedString(@"account_ready", @"Your account is ready to be used.");
    self.accountActivatedLabel.text = NSLocalizedString(@"account_activated", @"Your account is activated!");
    self.rememberPinLabel.text = NSLocalizedString(@"remember_pincode_notice", @"Remember your PIN, it cannot be changed!");
    
    self.title = NSLocalizedString(@"account_activation_title", @"Account activation title");
    UIBarButtonItem *backButton = [[[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemDone target:self action:@selector(done)] autorelease];
    self.navigationItem.leftBarButtonItem = backButton;
    
    self.identityProviderLogoImageView.image = [[UIImage alloc] initWithData:self.challenge.identityProviderLogo];
    self.identityDisplayNameLabel.text = self.challenge.identityDisplayName;
    self.identityProviderDisplayNameLabel.text = self.challenge.identityProviderDisplayName;
    
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
    self.returnButton = nil;
    self.accountReadyLabel = nil;
    self.accountActivatedLabel = nil;
    self.rememberPinLabel = nil;
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