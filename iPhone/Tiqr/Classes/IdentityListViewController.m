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

#import "IdentityListViewController.h"
#import "ScanViewController.h"
#import "TiqrAppDelegate.h"
#import "Identity.h"
#import "Identity+Utils.h"
#import "IdentityProvider.h"
#import "SecretStore.h"
#import "IdentityTableViewCell.h"
#import "IdentityEditViewController.h"

@interface IdentityListViewController ()

@property (nonatomic, retain) NSFetchedResultsController *fetchedResultsController;
@property (nonatomic, assign) BOOL processingMoveRow;

@end

@implementation IdentityListViewController

@synthesize fetchedResultsController=fetchedResultsController_, managedObjectContext=managedObjectContext_, processingMoveRow=processingMoveRow_;

- (id)init {
    self = [super initWithNibName:@"IdentityListView" bundle:nil];
    if (self != nil) {
        self.title = NSLocalizedString(@"your_accounts", @"Accounts navigation item title");
        self.navigationItem.backBarButtonItem = [[[UIBarButtonItem alloc] initWithTitle:NSLocalizedString(@"accounts", @"Accounts back button title") style:UIBarButtonItemStylePlain target:nil action:nil] autorelease];
        self.navigationItem.rightBarButtonItem = self.editButtonItem;
    }
    
    return self;
}

- (void)viewDidLoad {
    [super viewDidLoad];
    if ([self respondsToSelector:@selector(edgesForExtendedLayout)]) {
        self.edgesForExtendedLayout = UIRectEdgeNone;
    }
}

- (void)done {
    [self.navigationController popViewControllerAnimated:YES];
}

- (void)setEditing:(BOOL)editing animated:(BOOL)animated {
    [super setEditing:(BOOL)editing animated:(BOOL)animated];
    self.navigationItem.leftBarButtonItem.enabled = !editing;
}

- (void)configureCell:(IdentityTableViewCell *)cell atIndexPath:(NSIndexPath *)indexPath {
    Identity *identity = [self.fetchedResultsController objectAtIndexPath:indexPath];
	[cell setIdentity:identity];
	cell.accessoryType = UITableViewCellAccessoryDisclosureIndicator;
}

#pragma mark -
#pragma mark Table view data source

- (NSInteger)numberOfSectionsInTableView:(UITableView *)tableView {
    return [[self.fetchedResultsController sections] count];
}

- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section {
    id <NSFetchedResultsSectionInfo> sectionInfo = [[self.fetchedResultsController sections] objectAtIndex:section];
    return [sectionInfo numberOfObjects];
}

- (CGFloat)tableView:(UITableView *)tableView heightForRowAtIndexPath:(NSIndexPath *)indexPath {
	return 80.0;
}

- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath {
    static NSString *CellIdentifier = @"Cell";
    
    IdentityTableViewCell *cell = (IdentityTableViewCell *)[tableView dequeueReusableCellWithIdentifier:CellIdentifier];
    if (cell == nil) {
        cell = [[[IdentityTableViewCell alloc] initWithReuseIdentifier:CellIdentifier] autorelease];
    }
    
    [self configureCell:cell atIndexPath:indexPath];
    
    return cell;
}

- (void)tableView:(UITableView *)tableView didSelectRowAtIndexPath:(NSIndexPath *)indexPath {
    Identity *identity = [self.fetchedResultsController objectAtIndexPath:indexPath];
    IdentityEditViewController *viewController = [[IdentityEditViewController alloc] initWithIdentity:identity];
    [self.navigationController pushViewController:viewController animated:YES];
    [viewController release];
}

- (void)tableView:(UITableView *)tableView commitEditingStyle:(UITableViewCellEditingStyle)editingStyle forRowAtIndexPath:(NSIndexPath *)indexPath {
    if (editingStyle == UITableViewCellEditingStyleDelete) {
        NSManagedObjectContext *context = [self.fetchedResultsController managedObjectContext];
		
		Identity *identity = [self.fetchedResultsController objectAtIndexPath:indexPath];
		IdentityProvider *identityProvider = identity.identityProvider;

        SecretStore *store = nil;       
        if (identityProvider != nil) {
            store = [SecretStore secretStoreForIdentity:identity.identifier identityProvider:identityProvider.identifier];		
		
            [identityProvider removeIdentitiesObject:identity];
            [context deleteObject:identity];
            if ([identityProvider.identities count] == 0) {
                [context deleteObject:identityProvider];
            }
        } else {
            [context deleteObject:identity];            
        }
        
        NSError *error = nil;
        if ([context save:&error]) {
            if (store != nil) {
                [store deleteFromKeychain];
            }
        } else {
            NSLog(@"Unexpected error: %@", error);
            NSString *title = NSLocalizedString(@"error", "Alert title for error");		
            NSString *message = NSLocalizedString(@"error_auth_unknown_error", "Unexpected error message");		        
            NSString *okTitle = NSLocalizedString(@"ok_button", "OK button title");		
            UIAlertView *alertView = [[[UIAlertView alloc] initWithTitle:title message:message delegate:self cancelButtonTitle:okTitle otherButtonTitles:nil] autorelease];
            [alertView show];
            [alertView release];
		}
    }   
}

- (BOOL)tableView:(UITableView *)tableView canMoveRowAtIndexPath:(NSIndexPath *)indexPath {
    return YES;
}

- (void)tableView:(UITableView *)tableView moveRowAtIndexPath:(NSIndexPath *)fromIndexPath toIndexPath:(NSIndexPath *)toIndexPath {
	self.processingMoveRow = YES;
	
	NSMutableArray *fetchedObjects = [NSMutableArray arrayWithArray:[self.fetchedResultsController fetchedObjects]];  	
	id movedObject = [fetchedObjects objectAtIndex:fromIndexPath.row];
	[fetchedObjects removeObjectAtIndex:fromIndexPath.row];
	[fetchedObjects insertObject:movedObject atIndex:toIndexPath.row];
	
    NSManagedObjectContext *context = [self.fetchedResultsController managedObjectContext];
	NSUInteger sortIndex = 0;
	for (Identity *identity in fetchedObjects) {
		identity.sortIndex = [NSNumber numberWithInt:sortIndex];
		sortIndex++;
	}
	
    NSError *error = nil;
    if (![context save:&error]) {
        NSLog(@"Unexpected error: %@", error);
        NSString *title = NSLocalizedString(@"error", "Alert title for error");		
        NSString *message = NSLocalizedString(@"error_auth_unknown_error", "Unexpected error message");		        
        NSString *okTitle = NSLocalizedString(@"ok_button", "OK button title");			
		UIAlertView *alertView = [[[UIAlertView alloc] initWithTitle:title message:message delegate:self cancelButtonTitle:okTitle otherButtonTitles:nil] autorelease];
		[alertView show];
        [alertView release];
    }
	
	self.processingMoveRow = NO;	
}

#pragma mark -
#pragma mark Fetched results controller

- (NSFetchedResultsController *)fetchedResultsController {
    if (fetchedResultsController_ != nil) {
        return fetchedResultsController_;
    }
    
    NSFetchRequest *fetchRequest = [[NSFetchRequest alloc] init];
    NSEntityDescription *entity = [NSEntityDescription entityForName:@"Identity" inManagedObjectContext:self.managedObjectContext];
    [fetchRequest setEntity:entity];
    [fetchRequest setFetchBatchSize:20];
    
    NSSortDescriptor *sortDescriptor = [[NSSortDescriptor alloc] initWithKey:@"sortIndex" ascending:YES];
    NSArray *sortDescriptors = [[NSArray alloc] initWithObjects:sortDescriptor, nil];
    [fetchRequest setSortDescriptors:sortDescriptors];
    [sortDescriptor release];
    [sortDescriptors release];
    
    NSFetchedResultsController *fetchedResultsController = [[NSFetchedResultsController alloc] initWithFetchRequest:fetchRequest managedObjectContext:self.managedObjectContext sectionNameKeyPath:nil cacheName:@"Root"];
    fetchedResultsController.delegate = self;
    self.fetchedResultsController = fetchedResultsController;
    
    [fetchedResultsController release];
    [fetchRequest release];
    
    NSError *error = nil;
    if (![fetchedResultsController_ performFetch:&error]) {
        NSLog(@"Unexpected error: %@", error);
        NSString *title = NSLocalizedString(@"error", "Alert title for error");		
        NSString *message = NSLocalizedString(@"error_auth_unknown_error", "Unexpected error message");		        
        NSString *okTitle = NSLocalizedString(@"ok_button", "OK button title");			
		UIAlertView *alertView = [[[UIAlertView alloc] initWithTitle:title message:message delegate:self cancelButtonTitle:okTitle otherButtonTitles:nil] autorelease];
		[alertView show];
        [alertView release];
    }
    
    return fetchedResultsController_;
}    

#pragma mark -
#pragma mark Fetched results controller delegate

- (void)controllerWillChangeContent:(NSFetchedResultsController *)controller {
	if (self.processingMoveRow) {
		return;
	}
	
    [self.tableView beginUpdates];
}

- (void)controller:(NSFetchedResultsController *)controller didChangeObject:(id)anObject
       atIndexPath:(NSIndexPath *)indexPath forChangeType:(NSFetchedResultsChangeType)type
      newIndexPath:(NSIndexPath *)newIndexPath {
	if (self.processingMoveRow) {
		return;
	}
	
    UITableView *tableView = self.tableView;
    
    switch(type) {
        case NSFetchedResultsChangeInsert:
            [tableView insertRowsAtIndexPaths:[NSArray arrayWithObject:newIndexPath] withRowAnimation:UITableViewRowAnimationFade];
            break;
            
        case NSFetchedResultsChangeDelete:
            [tableView deleteRowsAtIndexPaths:[NSArray arrayWithObject:indexPath] withRowAnimation:UITableViewRowAnimationFade];
            break;
            
        case NSFetchedResultsChangeUpdate:
            [self configureCell:(IdentityTableViewCell *)[tableView cellForRowAtIndexPath:indexPath] atIndexPath:indexPath];
			[tableView reloadData];
            break;
            
        case NSFetchedResultsChangeMove:
            [tableView deleteRowsAtIndexPaths:[NSArray arrayWithObject:indexPath] withRowAnimation:UITableViewRowAnimationFade];
            [tableView insertRowsAtIndexPaths:[NSArray arrayWithObject:newIndexPath]withRowAnimation:UITableViewRowAnimationFade];
            break;
    }
}

- (void)controllerDidChangeContent:(NSFetchedResultsController *)controller {
	if (self.processingMoveRow) {
		return;
	}
	
    [self.tableView endUpdates];
}

#pragma mark -
#pragma mark Memory management

- (void)didReceiveMemoryWarning {
    [super didReceiveMemoryWarning];
}

- (void)viewDidUnload {
    [super viewDidUnload];
}

- (void)dealloc {
    self.fetchedResultsController = nil;
	self.managedObjectContext = nil;
    [super dealloc];
}

@end