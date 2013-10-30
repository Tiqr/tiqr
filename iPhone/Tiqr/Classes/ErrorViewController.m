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

#import "ErrorViewController.h"

#import "ErrorController.h"
#import "FooterController.h"
#import "TiqrAppDelegate.h"

@interface ErrorViewController ()

@property (nonatomic, retain) ErrorController *errorController;
@property (nonatomic, retain) FooterController *footerController;

@end

@implementation ErrorViewController

@synthesize errorController=errorController_;
@synthesize footerController=footerController_;

- (id)initWithTitle:(NSString *)title errorTitle:(NSString *)errorTitle errorMessage:(NSString *)errorMessage {
    self = [super initWithNibName:@"ErrorView" bundle:nil];
    if (self != nil) {
        self.title = title;
        UIBarButtonItem *backBarButtonItem = [[[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemDone target:self action:@selector(done)] autorelease];
        self.navigationItem.leftBarButtonItem = backBarButtonItem;
        
        self.errorController = [[[ErrorController alloc] init] autorelease];
        self.errorController.title = errorTitle;
        self.errorController.message = errorMessage;
        
        self.footerController = [[[FooterController alloc] init] autorelease];
    }
    
    return self;
}

- (void)viewDidLoad {
    [super viewDidLoad];
    [self.errorController addToView:self.view];
    [self.footerController addToView:self.view];
    
    if ([self respondsToSelector:@selector(edgesForExtendedLayout)]) {
        self.edgesForExtendedLayout = UIRectEdgeNone;
    }
}

- (void)done {
    [(TiqrAppDelegate *)[UIApplication sharedApplication].delegate popToStartViewControllerAnimated:YES];
}

- (void)viewDidUnload {
    [super viewDidUnload];
    [self.errorController.view removeFromSuperview];
    [self.footerController.view removeFromSuperview];
}

- (void)dealloc {
    self.errorController = nil;
    self.footerController = nil;
    [super dealloc];
}

@end
