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

#import "PINViewController.h"
#import "PINViewController-Protected.h"
#import "ErrorController.h"
#import "NSString+Verhoeff.h"

@interface PINViewController ()

@property (nonatomic, retain) NSTimer *pin4Timer;
@property (nonatomic, retain) ErrorController *errorController;
@property (nonatomic, retain) IBOutlet UIButton *confirmButton;

@end

@implementation PINViewController

@synthesize delegate=delegate_;

@synthesize subtitleLabel=subtitleLabel_;
@synthesize descriptionLabel=descriptionLabel_;
@synthesize pinField=pinField_;
@synthesize pin1Field=pin1Field_, pin2Field=pin2Field_, pin3Field=pin3Field_, pin4Field=pin4Field_;
@synthesize okButton=okButton_;
@synthesize pin4Timer=pin4Timer_;
@synthesize errorController=errorController_;
@synthesize confirmButton=confirmButton_;

- (id)init {
    self = [super initWithNibName:@"PINView" bundle:nil];
    if (self != nil) {
        self.errorController = [[[ErrorController alloc] init] autorelease];
    }
    return self;
}

- (NSString *)subtitle {
    return self.subtitleLabel.text;
}

- (void)setSubtitle:(NSString *)subtitle {
    self.subtitleLabel.text = subtitle;
}

- (NSString *)description {
    return self.descriptionLabel.text;
}

- (void)setDescription:(NSString *)description {
    self.descriptionLabel.text = description;
}

- (void)viewDidLoad {
    [super viewDidLoad];
    
    [self.confirmButton setTitle:NSLocalizedString(@"ok_button", @"OK Button") forState:UIControlStateNormal];
    
    self.pinField.delegate = self;
    self.pin1Field.font = [UIFont fontWithName:@"Afrika Wildlife B Mammals2" size:24.0];
    self.pin2Field.font = [UIFont fontWithName:@"Afrika Wildlife B Mammals2" size:24.0];
    self.pin3Field.font = [UIFont fontWithName:@"Afrika Wildlife B Mammals2" size:24.0];    
    self.pin4Field.font = [UIFont fontWithName:@"Afrika Wildlife B Mammals2" size:24.0];
    
    if ([self respondsToSelector:@selector(edgesForExtendedLayout)]) {
        self.edgesForExtendedLayout = UIRectEdgeNone;
    }
    
    self.errorController.view.hidden = YES;
    [self.errorController addToView:self.view];
}

- (void)viewWillAppear:(BOOL)animated {
    [super viewWillAppear:animated];    
 
    [self clear];
    [self.pinField becomeFirstResponder];    
}

- (void)viewWillDisappear:(BOOL)animated {
    [self.pin4Timer invalidate];
    self.pin4Timer = nil;    
}

- (void)securePIN4Field {
    self.pin4Field.secureTextEntry = YES;
}

- (NSString *)verificationCharForPIN:(NSString *)PIN {
    NSString *table = @"$',^onljDP";
    NSInteger location = [PIN verhoeffDigit];
    return [table substringWithRange:NSMakeRange(location, 1)];
}

- (BOOL)textField:(UITextField *)pinField shouldChangeCharactersInRange:(NSRange)range replacementString:(NSString *)string {
    NSString *text = [pinField.text stringByReplacingCharactersInRange:range withString:string];
    if ([text length] > 4) {
        return NO;
    }
    
    [self.pin4Timer invalidate];
    self.pin4Timer = nil;
    
    NSString *verificationChar = [self verificationCharForPIN:text];
    
    if ([text length] == 0) {
        self.pin1Field.text = @"";
        self.pin2Field.text = @"";        
        self.pin3Field.text = @"";        
        self.pin4Field.text = @"";        
    } else if ([text length] == 1) {
        self.pin1Field.text = verificationChar;
        self.pin2Field.text = @"";        
        self.pin3Field.text = @"";        
        self.pin4Field.text = @"";        
    } else if ([text length] == 2) {
        self.pin1Field.text = @"x";
        self.pin2Field.text = verificationChar;        
        self.pin3Field.text = @"";        
        self.pin4Field.text = @"";        
    } else if ([text length] == 3) {
        self.pin1Field.text = @"x";
        self.pin2Field.text = @"x";        
        self.pin3Field.text = verificationChar;        
        self.pin4Field.text = @"";        
    } else if ([text length] == 4) {
        self.pin1Field.text = @"x";
        self.pin2Field.text = @"x";        
        self.pin3Field.text = @"x";        
        self.pin4Field.text = verificationChar;        
    }
    
    self.pin1Field.secureTextEntry = [text length] > 1;
    self.pin2Field.secureTextEntry = [text length] > 2;    
    self.pin3Field.secureTextEntry = [text length] > 3;    
    self.pin4Field.secureTextEntry = NO;
    if ([text length] == 4) {
        self.pin4Timer = [NSTimer scheduledTimerWithTimeInterval:2.0 target:self selector:@selector(securePIN4Field) userInfo:nil repeats:NO];
    }
    
    self.okButton.enabled = [text length] == 4;
    
    return YES;
}

- (void)ok {
    [self dismissModalViewControllerAnimated:YES];
    NSString *pin = self.pinField.text;
    [self.delegate PINViewController:self didFinishWithPIN:pin];
}

- (void)showErrorWithTitle:(NSString *)title message:(NSString *)message {
    self.errorController.title = title;
    self.errorController.message = message;
    self.errorController.view.hidden = NO;    
}

- (void)clear {
    self.pinField.text = @"";
    self.pin1Field.text = @"";
    self.pin2Field.text = @"";    
    self.pin3Field.text = @"";    
    self.pin4Field.text = @"";    
    self.okButton.enabled = NO;  
}

- (void)resetOutlets {
    self.subtitleLabel = nil;
    self.descriptionLabel = nil;
    self.pinField = nil;
    self.pin1Field = nil;
    self.pin2Field = nil;    
    self.pin3Field = nil;    
    self.pin4Field = nil;
    self.okButton = nil;
}

- (void)viewDidUnload {
    [self resetOutlets];
    [super viewDidUnload];
}

- (void)dealloc {
    [self resetOutlets];    
    [self.pin4Timer invalidate];
    self.pin4Timer = nil;    
    [super dealloc];
}

@end
