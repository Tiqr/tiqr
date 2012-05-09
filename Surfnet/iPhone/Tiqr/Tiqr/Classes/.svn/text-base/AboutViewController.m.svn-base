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

@implementation AboutViewController

@synthesize versionLabel;

- (id)init {
    
    self = [super init];
    if (self != nil) {
        self.modalTransitionStyle = UIModalTransitionStyleFlipHorizontal;    
    }
    
    return self;
}

- (void)viewDidLoad {
    [super viewDidLoad];
    
    NSString *version = [[NSBundle mainBundle] objectForInfoDictionaryKey:@"CFBundleVersion"];
    self.versionLabel.text = [self.versionLabel.text stringByAppendingFormat:@" %@", version];
}

- (IBAction)tiqr {
    [[UIApplication sharedApplication] openURL:[NSURL URLWithString:@"https://tiqr.org/"]];    
}

- (IBAction)surfnet {
    [[UIApplication sharedApplication] openURL:[NSURL URLWithString:@"http://www.surfnet.nl/en/"]];    
}

- (IBAction)egeniq {
    [[UIApplication sharedApplication] openURL:[NSURL URLWithString:@"http://www.egeniq.com/"]];    
}

- (IBAction)stroomt {
    [[UIApplication sharedApplication] openURL:[NSURL URLWithString:@"http://www.stroomt.com/"]];
}

- (IBAction)done {
    [self dismissModalViewControllerAnimated:YES];
}

- (void)dealloc {
    
    self.versionLabel = nil;
    
    [super dealloc];
}

@end