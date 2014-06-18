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

#import "FooterController.h"
#import "FooterController-Protected.h"
#import "AboutViewController.h"
#import "TiqrAppDelegate.h"

@interface FooterController ()

@property (nonatomic, retain) IBOutlet UILabel *providedByLabel;

@end;

@implementation FooterController

@synthesize view=view_;
@synthesize providedByLabel=providedByLabel_;

- (id)init {
    self = [super init];
    if (self != nil) {
        UINib *nib = [UINib nibWithNibName:@"FooterChildView" bundle:nil];
        [nib instantiateWithOwner:self options:nil];
    }
    
    return self;
}

- (void)addToView:(UIView *)view {
    [self.view removeFromSuperview];
    
    self.providedByLabel.text = NSLocalizedString(@"provided_by", @"provided by:");
    self.view.frame = CGRectMake(0.0, view.frame.size.height - self.view.frame.size.height, view.frame.size.width, self.view.frame.size.height);
    self.view.autoresizingMask = UIViewAutoresizingFlexibleTopMargin|UIViewAutoresizingFlexibleWidth;
    [view addSubview:self.view];
}

- (void)tiqr {
    [[UIApplication sharedApplication] openURL:[NSURL URLWithString:@"https://tiqr.org/"]];
}

- (void)surfnet {
    [[UIApplication sharedApplication] openURL:[NSURL URLWithString:@"http://www.surfnet.nl/en/"]];    
}

- (void)about {
    UIViewController *viewController = [[AboutViewController alloc] init];
    TiqrAppDelegate *delegate = (TiqrAppDelegate *)[UIApplication sharedApplication].delegate;
    [delegate.navigationController presentViewController:viewController animated:YES completion:nil];
    [viewController release];
}

- (void)dealloc {
    self.view = nil;
    self.providedByLabel = nil;
    [super dealloc];
}

@end