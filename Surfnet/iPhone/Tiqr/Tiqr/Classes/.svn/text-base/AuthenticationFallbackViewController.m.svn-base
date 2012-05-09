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

@end

@implementation AuthenticationFallbackViewController

@synthesize managedObjectContext=managedObjectContext_;
@synthesize challenge=challenge_;
@synthesize response=response_;
@synthesize footerController=footerController_;
@synthesize identityIdentifierLabel=identityIdentifierLabel_;
@synthesize oneTimePasswordLabel=oneTimePasswordLabel_;

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
	
    self.title = NSLocalizedString(@"Login", @"Login title");
    self.navigationItem.leftBarButtonItem = [[[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemDone target:self action:@selector(done)] autorelease];        
	
	self.identityIdentifierLabel.text = self.challenge.identity.identifier;
    self.oneTimePasswordLabel.text = self.response; 
    
    [self.footerController addToView:self.view];    
}

- (void)done {
    [(TiqrAppDelegate *)[UIApplication sharedApplication].delegate popToStartViewControllerAnimated:YES];    
}

- (void)resetOutlets {
    self.identityIdentifierLabel = nil;
    self.oneTimePasswordLabel = nil;
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
