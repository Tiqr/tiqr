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

#import "IdentityTableViewCell.h"
#import "IdentityProvider.h"

@interface IdentityTableViewCell ()

@property (nonatomic, retain) UILabel *blockedLabel;

@end

@implementation IdentityTableViewCell

@synthesize blockedLabel=blockedLabel_;

- (id)initWithReuseIdentifier:(NSString *)reuseIdentifier {
    self = [super initWithStyle:UITableViewCellStyleSubtitle reuseIdentifier:reuseIdentifier];
    if (self) {
		self.imageView.contentMode = UIViewContentModeScaleAspectFit;
		self.imageView.bounds = CGRectMake(5.0, 5.0, 10.0, 10.0);
        
        self.blockedLabel = [[[UILabel alloc] initWithFrame:CGRectMake(0.0, 0.0, 100.0, 20.0)] autorelease];
        self.blockedLabel.font = [UIFont boldSystemFontOfSize:16.0];
        self.blockedLabel.text = NSLocalizedString(@"identity_blocked", @"Blocked cell label");
        self.blockedLabel.textColor = [UIColor redColor];
        [self addSubview:self.blockedLabel];
    }
    return self;
}

- (void)layoutSubviews {
	[super layoutSubviews];		
	
	CGRect imageFrame = self.imageView.frame;
	CGSize imageSize = self.imageView.image.size;
	
	CGFloat padding = 5;
	CGFloat maxWidth = 80;
	CGFloat maxHeight = self.frame.size.height - (2 * padding);
	
	CGFloat width = imageSize.width;
	CGFloat height = imageSize.height;	
	
	if (width / maxWidth > height / maxHeight) {
		width = maxWidth;
		height = (width / imageSize.width) * height;
	} else {
		height = maxHeight;
		width = (height / imageSize.height) * width;
	}
	
	imageFrame.origin.x = padding + ((maxWidth - width) / 2);
	imageFrame.origin.y = padding + ((maxHeight - height) / 2);	
	imageFrame.size.width = width;
	imageFrame.size.height = height;	
	self.imageView.frame = imageFrame;
	
	CGFloat textPaddingX = 10.0;
    CGFloat textPaddingY = 0.0;
	CGFloat textOriginX = padding + maxWidth + textPaddingX;
	CGFloat textBlockHeight = self.textLabel.frame.size.height + textPaddingY + self.detailTextLabel.frame.size.height + (self.blockedLabel.hidden ? 0.0 : textPaddingY + self.blockedLabel.frame.size.height);
    CGFloat textOriginY = (self.frame.size.height - textBlockHeight) / 2.0;
    
	CGRect textFrame = self.textLabel.frame;
	textFrame.origin.x = textOriginX;	
    textFrame.origin.y = textOriginY;
	textFrame.size.width = self.frame.size.width - textFrame.origin.x - textPaddingX - 20.0;
	self.textLabel.frame = textFrame;
	
	CGRect detailTextFrame = self.detailTextLabel.frame;
	detailTextFrame.origin.x = textOriginX;	
    detailTextFrame.origin.y = textOriginY + textFrame.size.height + textPaddingY;
	detailTextFrame.size.width = self.frame.size.width - detailTextFrame.origin.x - textPaddingX - 20.0;
	self.detailTextLabel.frame = detailTextFrame;
    
	CGRect blockedFrame = self.blockedLabel.frame;
	blockedFrame.origin.x = textOriginX;	
    blockedFrame.origin.y = textOriginY + textFrame.size.height + textPaddingY + detailTextFrame.size.height + textPaddingY;
	blockedFrame.size.width = self.frame.size.width - blockedFrame.origin.x - textPaddingX - 20.0;
	self.blockedLabel.frame = blockedFrame;
}

- (void)setIdentity:(Identity *)identity {
	self.textLabel.text = identity.displayName;
	self.detailTextLabel.text = identity.identityProvider.displayName;
    self.blockedLabel.hidden = ![identity.blocked boolValue];
	UIImage *image = [[UIImage alloc] initWithData:identity.identityProvider.logo];
	self.imageView.image = image;
	[image release];
}

- (void)setSelected:(BOOL)selected animated:(BOOL)animated {
    [super setSelected:selected animated:animated];
}

- (void)dealloc {
    self.blockedLabel = nil;
    [super dealloc];
}

@end