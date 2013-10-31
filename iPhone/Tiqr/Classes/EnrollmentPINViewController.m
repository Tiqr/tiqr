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

#import "EnrollmentPINViewController.h"
#import "EnrollmentPINVerificationViewController.h"

@interface EnrollmentPINViewController ()

@property (nonatomic, retain) EnrollmentChallenge *challenge;

@end

@implementation EnrollmentPINViewController

@synthesize managedObjectContext=managedObjectContext_;
@synthesize challenge=challenge_;

- (id)initWithEnrollmentChallenge:(EnrollmentChallenge *)challenge {
    self = [super init];
    if (self != nil) {
        self.challenge = challenge;
        self.delegate = self;
    }
	
	return self;
}

- (void)viewDidLoad {
    [super viewDidLoad];

    self.title = NSLocalizedString(@"enrollment_confirmation_header_title", @"Account activation title");
    self.navigationItem.backBarButtonItem = [[[UIBarButtonItem alloc] initWithTitle:NSLocalizedString(@"password_verify_back_button", @"Enrollment PIN back button title") style:UIBarButtonItemStyleBordered target:nil action:nil] autorelease];        
    self.subtitle = NSLocalizedString(@"enrollment_pin_intro", @"Enrollment PIN title");
    self.description = NSLocalizedString(@"enrollment_pin_message", @"You need a PIN code for this account. If you don't yet have a PIN code for tiqr please choose one.");
}

- (void)PINViewController:(PINViewController *)pinViewController didFinishWithPIN:(NSString *)PIN {
    EnrollmentPINVerificationViewController *viewController = [[EnrollmentPINVerificationViewController alloc] initWithEnrollmentChallenge:self.challenge PIN:PIN];
    viewController.managedObjectContext = self.managedObjectContext;
    [self.navigationController pushViewController:viewController animated:YES];
    [viewController release];
}

- (void)dealloc {
    self.challenge = nil;
    self.managedObjectContext = nil;
    [super dealloc];
}

@end
