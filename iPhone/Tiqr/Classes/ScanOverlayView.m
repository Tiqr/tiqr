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

/**
 * Based on ZXingWidgetController.
 *
 * Copyright 2009 Jeff Verkoeyen
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

#import "ScanOverlayView.h"

#define kPadding 10

@interface ScanOverlayView ()

@property (nonatomic, assign) CGRect cropRect;

@end

@implementation ScanOverlayView

@synthesize cropRect=cropRect_;
@synthesize points=points_;

- (void)setFrame:(CGRect)frame {
	[super setFrame:frame];
	
    CGFloat rectSize = self.frame.size.width - kPadding * 2;
	self.cropRect = CGRectMake(kPadding, (self.frame.size.height - rectSize) / 2, rectSize, rectSize);
}

- (void)drawRect:(CGRect)rect inContext:(CGContextRef)context {
	CGContextBeginPath(context);
	CGContextMoveToPoint(context, rect.origin.x, rect.origin.y);
	CGContextAddLineToPoint(context, rect.origin.x + rect.size.width, rect.origin.y);
	CGContextAddLineToPoint(context, rect.origin.x + rect.size.width, rect.origin.y + rect.size.height);
	CGContextAddLineToPoint(context, rect.origin.x, rect.origin.y + rect.size.height);
	CGContextAddLineToPoint(context, rect.origin.x, rect.origin.y);
	CGContextStrokePath(context);
}

- (CGPoint)map:(CGPoint)point {
    CGPoint center;
    center.x = self.cropRect.size.width / 2;
    center.y = self.cropRect.size.height / 2;
    float x = point.x - center.x;
    float y = point.y - center.y;
    int rotation = 90;
    switch(rotation) {
		case 0:
			point.x = x;
			point.y = y;
			break;
		case 90:
			point.x = -y;
			point.y = x;
			break;
		case 180:
			point.x = -x;
			point.y = -y;
			break;
		case 270:
			point.x = y;
			point.y = -x;
			break;
    }
    point.x = point.x + center.x;
    point.y = point.y + center.y;
    return point;
}

- (void)drawRect:(CGRect)rect {
	CGContextRef context = UIGraphicsGetCurrentContext();
	
	CGFloat white[4] = { 1.0f, 1.0f, 1.0f, 1.0f };
	CGContextSetStrokeColor(context, white);
	CGContextSetFillColor(context, white);
	[self drawRect:self.cropRect inContext:context];
	
	if (self.points != nil) {
		CGFloat green[4] = { 0.0f, 1.0f, 0.0f, 1.0f };
		CGContextSetStrokeColor(context, green);
		CGContextSetFillColor(context, green);
		CGRect smallSquare = CGRectMake(0, 0, 10, 10);
		for (NSValue *value in self.points) {
			CGPoint point = [self map:[value CGPointValue]];
			smallSquare.origin = CGPointMake(self.cropRect.origin.x + point.x - smallSquare.size.width / 2, 
                                             self.cropRect.origin.y + point.y - smallSquare.size.height / 2);
			[self drawRect:smallSquare inContext:context];
		}
	}
}

- (void)setPoints:(NSArray *)points {
    [points_ release];
    points_ = [points retain];
    [self setNeedsDisplay];
}

- (void)addPoint:(CGPoint)point {
    NSMutableArray *points = [NSMutableArray arrayWithArray:(self.points == nil ? [NSArray array] : self.points)];
    
    if ([points count] > 3) {
        [points removeObjectAtIndex:0];
    }
    
    [points addObject:[NSValue valueWithCGPoint:point]];
    self.points = [points copy];
    
    [self setNeedsDisplay];
}

- (void)dealloc {
    self.points = nil;
    [super dealloc];
}

@end