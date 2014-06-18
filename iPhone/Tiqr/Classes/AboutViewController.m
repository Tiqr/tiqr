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

#import "AboutViewController.h"
#import "AboutViewController-Protected.h"

@interface AboutViewController ()

@property (nonatomic, retain) IBOutlet UILabel *tiqrProvidedByLabel;
@property (nonatomic, retain) IBOutlet UILabel *developedByLabel;
@property (nonatomic, retain) IBOutlet UILabel *interactionDesignLabel;
@property (nonatomic, retain) IBOutlet UINavigationItem *barItem;
@property (nonatomic, retain) IBOutlet UIButton *okButton;

@end

@implementation AboutViewController

@synthesize versionLabel;
@synthesize tiqrProvidedByLabel=tiqrProvidedByLabel_;
@synthesize developedByLabel=developedByLabel_;
@synthesize interactionDesignLabel=interactionDesignLabel_;
@synthesize okButton=okButton_;

- (id)init {
    
    self = [super init];
    if (self != nil) {
        self.modalTransitionStyle = UIModalTransitionStyleFlipHorizontal;    
    }
    
    return self;
}

- (void)viewDidLoad {
    [super viewDidLoad];
    
    self.barItem.title = NSLocalizedString(@"about_title", @"About tiqr");
    self.tiqrProvidedByLabel.text = NSLocalizedString(@"provided_by_title", @"tiqr is provided by:");
    self.developedByLabel.text = NSLocalizedString(@"developed_by_title", @"Developed by:");
    self.interactionDesignLabel.text = NSLocalizedString(@"interaction_by_title", @"Interaction design:");
    
    [self.okButton setTitle:NSLocalizedString(@"ok_button", @"OK") forState:UIControlStateNormal];
    self.okButton.layer.borderWidth = 1;
    self.okButton.layer.borderColor = [UIColor colorWithRed:0 green:122.0/255.0 blue:1 alpha:1].CGColor;
    self.okButton.layer.cornerRadius = 4;
    
    NSString *version = [[NSBundle mainBundle] objectForInfoDictionaryKey:@"CFBundleVersion"];
    self.versionLabel.text = [self.versionLabel.text stringByAppendingFormat:@" %@", version];
    
    if ([self respondsToSelector:@selector(edgesForExtendedLayout)]) {
        self.edgesForExtendedLayout = UIRectEdgeNone;
    }
}

- (IBAction)tiqr {
    [[UIApplication sharedApplication] openURL:[NSURL URLWithString:@"https://tiqr.org/"]];    
}

- (IBAction)surfnet {
    [[UIApplication sharedApplication] openURL:[NSURL URLWithString:@"http://www.surfnet.nl/en/"]];    
}

- (IBAction)egeniq {
    [[UIApplication sharedApplication] openURL:[NSURL URLWithString:@"http://www.egeniq.com/?utm_source=tiqr&utm_medium=referral&utm_campaign=about"]];    
}

- (IBAction)stroomt {
    [[UIApplication sharedApplication] openURL:[NSURL URLWithString:@"http://www.stroomt.com/"]];
}

- (IBAction)done {
    [self dismissModalViewControllerAnimated:YES];
}

- (void)dealloc {
    
    self.tiqrProvidedByLabel = nil;
    self.developedByLabel = nil;
    self.interactionDesignLabel = nil;
    self.versionLabel = nil;
    
    [super dealloc];
}

@end