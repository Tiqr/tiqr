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

#import "EnrollmentConfirmViewController.h"
#import "EnrollmentConfirmViewController-Protected.h"
#import "EnrollmentPINViewController.h"

@interface EnrollmentConfirmViewController ()

@property (nonatomic, retain) EnrollmentChallenge *challenge;
@property (nonatomic, retain) IBOutlet UILabel *confirmAccountLabel;
@property (nonatomic, retain) IBOutlet UILabel *activateAccountLabel;
@property (nonatomic, retain) IBOutlet UILabel *enrollDomainLabel;
@property (nonatomic, retain) IBOutlet UIButton *okButton;

@end

@implementation EnrollmentConfirmViewController

@synthesize challenge=challenge_;
@synthesize managedObjectContext=managedObjectContext_;
@synthesize identityProviderLogoImageView=identityProviderLogoImageView_, identityDisplayNameLabel=identityDisplayNameLabel_, identityProviderDisplayNameLabel=identityProviderDisplayNameLabel_, enrollmentURLDomainLabel=enrollmentURLDomainLabel_;
@synthesize confirmAccountLabel=confirmAccountLabel_,activateAccountLabel=activateAccountLabel_,enrollDomainLabel=enrollDomainLabel_,okButton=okButton_;

- (id)initWithEnrollmentChallenge:(EnrollmentChallenge *)challenge {
    self = [super initWithNibName:@"EnrollmentConfirmView" bundle:nil];
	if (self != nil) {
		self.challenge = challenge;
	}
	
	return self;
}

- (void)viewDidLoad {
    [super viewDidLoad];

    self.confirmAccountLabel.text = NSLocalizedString(@"confirm_account_activation", @"Confirm account activation");
    self.activateAccountLabel.text = NSLocalizedString(@"activate_following_account", @"Do you want to activate the following account");
    self.enrollDomainLabel.text = NSLocalizedString(@"enroll_following_domain", @"You will enroll to the following domain");
    [self.okButton setTitle:NSLocalizedString(@"ok_button", @"OK") forState:UIControlStateNormal];
    self.okButton.layer.borderWidth = 1;
    self.okButton.layer.borderColor = [UIColor colorWithRed:0 green:122.0/255.0 blue:1 alpha:1].CGColor;
    self.okButton.layer.cornerRadius = 4;
    
    self.title = NSLocalizedString(@"enrollment_confirmation_header_title", @"Account activation title");
    self.navigationItem.backBarButtonItem = [[[UIBarButtonItem alloc] initWithTitle:NSLocalizedString(@"enrollment_confirmation_title", @"Enrollment confirm back button title") style:UIBarButtonItemStyleBordered target:nil action:nil] autorelease];        

    self.identityProviderLogoImageView.image = [[UIImage alloc] initWithData:self.challenge.identityProviderLogo];
    self.identityDisplayNameLabel.text = self.challenge.identityDisplayName;
    self.identityProviderDisplayNameLabel.text = self.challenge.identityProviderDisplayName;
    self.enrollmentURLDomainLabel.text = [[NSURL URLWithString:self.challenge.enrollmentUrl] host];
}

- (void)ok {
    EnrollmentPINViewController *viewController = [[EnrollmentPINViewController alloc] initWithEnrollmentChallenge:self.challenge];
    viewController.managedObjectContext = self.managedObjectContext;
    [self.navigationController pushViewController:viewController animated:YES];
    [viewController release];    
}

- (void)resetOutlets {
	self.identityProviderLogoImageView = nil;
	self.identityDisplayNameLabel = nil;
	self.identityProviderDisplayNameLabel = nil;
    self.confirmAccountLabel = nil;
    self.activateAccountLabel = nil;
    self.enrollDomainLabel = nil;
    self.okButton = nil;
}

- (void)viewDidUnload {
    [self resetOutlets];
    [super viewDidUnload];
}

- (void)dealloc {
    [self resetOutlets];
    
    self.challenge = nil;
    self.managedObjectContext = nil;
    
    [super dealloc];	
}

@end