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

#import "AuthenticationFallbackViewController.h"
#import "AuthenticationFallbackViewController-Protected.h"
#import "FooterController.h"
#import "TiqrAppDelegate.h"

@interface AuthenticationFallbackViewController ()

@property (nonatomic, retain) AuthenticationChallenge *challenge;
@property (nonatomic, copy) NSString *response;
@property (nonatomic, retain) FooterController *footerController;
@property (nonatomic, retain) IBOutlet UILabel *errorTitleLabel;
@property (nonatomic, retain) IBOutlet UILabel *errorInstructionLabel;
@property (nonatomic, retain) IBOutlet UILabel *yourIdLabel;
@property (nonatomic, retain) IBOutlet UILabel *oneTimeLoginCodeLabel;
@property (nonatomic, retain) IBOutlet UILabel *unverifiedPinLabel;
@property (nonatomic, retain) IBOutlet UILabel *retryLabel;

@end

@implementation AuthenticationFallbackViewController

@synthesize managedObjectContext=managedObjectContext_;
@synthesize challenge=challenge_;
@synthesize response=response_;
@synthesize footerController=footerController_;
@synthesize identityIdentifierLabel=identityIdentifierLabel_;
@synthesize oneTimePasswordLabel=oneTimePasswordLabel_;

@synthesize errorTitleLabel=errorTitleLabel_;
@synthesize errorInstructionLabel=errorInstructionLabel_;
@synthesize yourIdLabel=yourIdLabel_;
@synthesize oneTimeLoginCodeLabel=oneTimeLoginCodeLabel_;
@synthesize unverifiedPinLabel=unverifiedPinLabel_;
@synthesize retryLabel=retryLabel_;

- (id)initWithAuthenticationChallenge:(AuthenticationChallenge *)challenge response:(NSString *)response {
    self = [super initWithNibName:@"AuthenticationFallbackView" bundle:nil];   
	if (self != nil) {
		self.challenge = challenge;
        self.response = response;
        self.footerController = [[[FooterController alloc] init] autorelease];
	}
	
	return self;
}

- (void)viewDidLoad {
    [super viewDidLoad];
	
    self.errorTitleLabel.text = NSLocalizedString(@"authentication_fallback_title", @"You appear to be offline");
    self.errorInstructionLabel.text = NSLocalizedString(@"authentication_fallback_description", @"Don\'t worry! Click the QR tag on the\nwebsite. You will be asked to enter the\nfollowing one-time credentials:");
    self.yourIdLabel.text = NSLocalizedString(@"fallback_identifier_label", @"Your ID is:");
    self.oneTimeLoginCodeLabel.text = NSLocalizedString(@"otp_label", @"One time password:");
    self.unverifiedPinLabel.text = NSLocalizedString(@"note_pin_not_verified_title", @"Note: your PIN has not been verified yet.");
    self.retryLabel.text = NSLocalizedString(@"note_pin_not_verified", @"If you can\'t login with the credentials above, scan\nagain and enter the correct PIN code.");
    
    self.title = NSLocalizedString(@"authentication_title", @"Login title");
    self.navigationItem.leftBarButtonItem = [[[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemDone target:self action:@selector(done)] autorelease];        
	
	self.identityIdentifierLabel.text = self.challenge.identity.identifier;
    self.oneTimePasswordLabel.text = self.response; 
    
    [self.footerController addToView:self.view];
    
    if ([self respondsToSelector:@selector(edgesForExtendedLayout)]) {
        self.edgesForExtendedLayout = UIRectEdgeNone;
    }
}

- (void)done {
    [(TiqrAppDelegate *)[UIApplication sharedApplication].delegate popToStartViewControllerAnimated:YES];    
}

- (void)resetOutlets {
    self.identityIdentifierLabel = nil;
    self.oneTimePasswordLabel = nil;
    self.errorTitleLabel = nil;
    self.errorInstructionLabel = nil;
    self.yourIdLabel = nil;
    self.oneTimeLoginCodeLabel = nil;
    self.unverifiedPinLabel = nil;
    self.retryLabel = nil;
}

- (void)viewDidUnload {
    [self resetOutlets];
    [super viewDidUnload];
}

- (void)dealloc {
    [self resetOutlets];
    
    self.challenge = nil;
    self.response = nil;
    self.footerController = nil;
    self.managedObjectContext = nil;
	
    [super dealloc];	
}

@end
