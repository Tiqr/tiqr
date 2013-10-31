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

#import "AuthenticationIdentityViewController.h"
#import "AuthenticationIdentityViewController-Protected.h"
#import "AuthenticationConfirmViewController.h"
#import "Identity.h"
#import "IdentityTableViewCell.h"

@interface AuthenticationIdentityViewController ()

@property (nonatomic, retain) AuthenticationChallenge *challenge;
@property (nonatomic, retain) IBOutlet UILabel *selectAccountLabel;

@end

@implementation AuthenticationIdentityViewController

@synthesize challenge=challenge_;
@synthesize managedObjectContext=managedObjectContext_;
@synthesize tableView=tableView_;
@synthesize selectAccountLabel=selectAccountLabel_;

- (id)initWithAuthenticationChallenge:(AuthenticationChallenge *)challenge {
    self = [super initWithNibName:@"AuthenticationIdentityView" bundle:nil];
	if (self != nil) {
		self.challenge = challenge;
	}
	
	return self;
}

- (void)viewDidLoad {
    [super viewDidLoad];
    
    self.selectAccountLabel.text = NSLocalizedString(@"select_identity_title", @"Select Identity");
    
    self.title = NSLocalizedString(@"authentication_title", @"Login title");
    self.navigationItem.backBarButtonItem = [[[UIBarButtonItem alloc] initWithTitle:NSLocalizedString(@"identity_title", @"Identity select back button title") style:UIBarButtonItemStyleBordered target:nil action:nil] autorelease];
    
    if ([self respondsToSelector:@selector(edgesForExtendedLayout)]) {
        self.edgesForExtendedLayout = UIRectEdgeNone;
    }
}

- (NSInteger)numberOfSectionsInTableView:(UITableView *)tableView {
    return 1;
}

- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section {
	return [self.challenge.identities count];
}

- (CGFloat)tableView:(UITableView *)tableView heightForRowAtIndexPath:(NSIndexPath *)indexPath {
	return 60.0;
}

- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath {
    static NSString *CellIdentifier = @"Cell";
	
	Identity *identity = [self.challenge.identities objectAtIndex:indexPath.row];
    
    IdentityTableViewCell *cell = (IdentityTableViewCell *)[tableView dequeueReusableCellWithIdentifier:CellIdentifier];
    if (cell == nil) {
        cell = [[[IdentityTableViewCell alloc] initWithReuseIdentifier:CellIdentifier] autorelease];
		[cell setIdentity:identity];
    }
    
    return cell;
}

- (void)tableView:(UITableView *)tableView didSelectRowAtIndexPath:(NSIndexPath *)indexPath {
	self.challenge.identity = [self.challenge.identities objectAtIndex:indexPath.row];
    AuthenticationConfirmViewController *viewController = [[AuthenticationConfirmViewController alloc] initWithAuthenticationChallenge:self.challenge];
    viewController.managedObjectContext = self.managedObjectContext;
	[self.navigationController pushViewController:viewController animated:YES];
	[viewController release];
}

- (void)didReceiveMemoryWarning {
    [super didReceiveMemoryWarning];
}

- (void)viewDidUnload {
    self.tableView = nil;
    [super viewDidUnload];
}

- (void)dealloc {
    self.tableView = nil;
    self.challenge = nil;
    self.managedObjectContext = nil;
    self.selectAccountLabel = nil;
    [super dealloc];
}

@end