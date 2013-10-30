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

#import <QuartzCore/QuartzCore.h>
#import <AudioToolbox/AudioToolbox.h>
#import <AVFoundation/AVFoundation.h>
#import "ScanViewController.h"
#import "ScanViewController-Protected.h"
#import "TwoDDecoderResult.h"
#import "QRCodeReader.h"
#import "Decoder.h"
#import "AuthenticationChallenge.h"
#import "EnrollmentChallenge.h"
#import "AuthenticationIdentityViewController.h"
#import "AuthenticationConfirmViewController.h"
#import "AuthenticationFallbackViewController.h"
#import "EnrollmentConfirmViewController.h"
#import "Identity+Utils.h"
#import "IdentityListViewController.h"
#import "ErrorViewController.h"
#import "MBProgressHUD.h"

@interface ScanViewController () <AVAudioPlayerDelegate>

#if HAS_AVFF
@property (nonatomic, retain) AVCaptureSession *captureSession;
@property (nonatomic, retain) AVCaptureVideoPreviewLayer *previewLayer;
#endif

@property (nonatomic, retain) AVAudioPlayer *audioPlayer;
@property (nonatomic, assign, getter=isDecoding) BOOL decoding;
@property (nonatomic, retain) UIBarButtonItem *identitiesButtonItem;

@property (nonatomic, retain) IBOutlet UILabel *instructionLabel;

- (void)initCapture;
- (void)stopCapture;
- (void)processChallenge:(NSString *)rawResult;

@end

@implementation ScanViewController

#if HAS_AVFF
@synthesize captureSession=captureSession_;
@synthesize previewLayer=previewLayer_;
#endif

@synthesize managedObjectContext=managedObjectContext_;
@synthesize previewView=previewView_;
@synthesize instructionsView=instructionsView_;
@synthesize overlayView=overlayView_;
@synthesize decoding=decoding_;
@synthesize audioPlayer=audioPlayer_;
@synthesize identitiesButtonItem=identitiesButtonItem_;

@synthesize instructionLabel=instructionLabel_;

- (id)init {
    self = [super initWithNibName:@"ScanView" bundle:nil];
    if (self) {
        self.title = NSLocalizedString(@"scan_window", @"Scan window title");
        self.navigationItem.hidesBackButton = YES;        
        self.decoding = NO;
        
		NSString *filePath = [[NSBundle mainBundle] pathForResource:@"cowbell" ofType:@"wav"];
		NSURL *fileURL = [NSURL fileURLWithPath:filePath isDirectory:NO];
        [[AVAudioSession sharedInstance] setCategory:AVAudioSessionCategoryAmbient error:nil];
        
		self.audioPlayer = [[[AVAudioPlayer alloc] initWithContentsOfURL:fileURL error:nil] autorelease];
		[self.audioPlayer prepareToPlay];  
        self.audioPlayer.delegate = self;
        
        self.identitiesButtonItem = [[[UIBarButtonItem alloc] initWithImage:[UIImage imageNamed:@"identities"] style:UIBarButtonItemStyleBordered target:self action:@selector(listIdentities)] autorelease];
        self.navigationItem.rightBarButtonItem = self.identitiesButtonItem;
    }
    
    return self;
}

- (void)setMixableAudioShouldDuckActive:(BOOL)active {
    UInt32 value = active ? 1 : 0;
    AudioSessionSetProperty(kAudioSessionProperty_OtherMixableAudioShouldDuck, sizeof(value), &value);
    AudioSessionSetActive(value);    
}

- (void)audioPlayerDidFinishPlaying:(AVAudioPlayer *)player successfully:(BOOL)flag {
    [self setMixableAudioShouldDuckActive:NO];
}

- (void)audioPlayerBeginInterruption:(AVAudioPlayer *)player {
    // Implementing this delegate method also automatically stops the ducking
}
- (void)audioPlayerEndInterruption:(AVAudioPlayer *)player {
    // Implementing this delegate method also automatically resumes the ducking
}

- (void)viewWillAppear:(BOOL)animated {
    [super viewWillAppear:animated];
    self.overlayView.points = nil;    
    
    self.instructionsView.alpha = 0.0;    
    
    if ([Identity countInManagedObjectContext:self.managedObjectContext] > 0) {
        self.navigationItem.rightBarButtonItem = self.identitiesButtonItem;
    } else {
        self.navigationItem.rightBarButtonItem = nil;
    }
}

- (void)viewDidLoad {
    [super viewDidLoad];
    self.instructionLabel.text = NSLocalizedString(@"msg_default_status", @"QR Code scan instruction");
}

- (void)viewDidAppear:(BOOL)animated {
    [super viewDidAppear:animated];
    self.decoding = YES;
    [self initCapture];    
    
    [UIView beginAnimations:nil context:nil];
    [UIView setAnimationDuration:0.5];
    [UIView setAnimationDelay:3.0];
    self.instructionsView.alpha = 0.7;
    [UIView commitAnimations];
    
    UIViewController *viewController = [[ErrorViewController alloc] initWithTitle:@"title" errorTitle:@"errortitle" errorMessage:@"error message"];
    [self.navigationController pushViewController:viewController animated:YES];
}

- (void)viewDidDisappear:(BOOL)animated {
    [super viewDidDisappear:animated];
    
    self.instructionsView.alpha = 0.0;      
    
    [self stopCapture];
}

#pragma mark -
#pragma mark Decoder delegates

- (void)decoder:(Decoder *)decoder willDecodeImage:(UIImage *)image usingSubset:(UIImage *)subset {
    
}

- (void)decoder:(Decoder *)decoder decodingImage:(UIImage *)image usingSubset:(UIImage *)subset {
    
}

- (void)decoder:(Decoder *)decoder didDecodeImage:(UIImage *)image usingSubset:(UIImage *)subset withResult:(TwoDDecoderResult *)twoDResult {
    decoder.delegate = nil;
    
    #ifdef HAS_AVFF
    [self.captureSession stopRunning];    
    #endif
    
    self.overlayView.points = [twoDResult points];
    
    [UIView beginAnimations:nil context:nil];
    [UIView setAnimationBeginsFromCurrentState:YES];    
    [UIView setAnimationDuration:0.3];
    self.instructionsView.alpha = 0.0;
    [UIView commitAnimations];
    
    // now, in a selector, call the delegate to give this overlay time to show the points
    [self performSelector:@selector(didScanResult:) withObject:[twoDResult text] afterDelay:1.0];
    [self setMixableAudioShouldDuckActive:YES];
	[self.audioPlayer play];    
}

- (void)didScanResult:(NSString *)result {
    [self processChallenge:result];
}

- (void)decoder:(Decoder *)decoder failedToDecodeImage:(UIImage *)image usingSubset:(UIImage *)subset reason:(NSString *)reason {
    decoder.delegate = nil;
    self.overlayView.points = nil;
}

- (void)decoder:(Decoder *)decoder foundPossibleResultPoint:(CGPoint)point {
    [self.overlayView addPoint:point];
}

#pragma mark - 
#pragma mark AVFoundation

- (void)initCapture {
    #if HAS_AVFF
    AVCaptureDevice *captureDevice = [AVCaptureDevice defaultDeviceWithMediaType:AVMediaTypeVideo];
    AVCaptureDeviceInput *captureInput = [AVCaptureDeviceInput deviceInputWithDevice:captureDevice error:nil];
    
    AVCaptureVideoDataOutput *captureOutput = [[[AVCaptureVideoDataOutput alloc] init] autorelease]; 
    captureOutput.alwaysDiscardsLateVideoFrames = YES; 
    [captureOutput setSampleBufferDelegate:self queue:dispatch_get_main_queue()];
    NSString *key = (NSString *)kCVPixelBufferPixelFormatTypeKey; 
    NSNumber *value = [NSNumber numberWithUnsignedInt:kCVPixelFormatType_32BGRA]; 
    NSDictionary *videoSettings = [NSDictionary dictionaryWithObject:value forKey:key]; 
    [captureOutput setVideoSettings:videoSettings]; 
    
    self.captureSession = [[[AVCaptureSession alloc] init] autorelease];
    self.captureSession.sessionPreset = AVCaptureSessionPresetMedium; // 480x360 on a 4
    [self.captureSession addInput:captureInput];
    [self.captureSession addOutput:captureOutput];
    
    self.previewLayer = [AVCaptureVideoPreviewLayer layerWithSession:self.captureSession];
    self.previewLayer.frame = self.view.bounds;
    self.previewLayer.videoGravity = AVLayerVideoGravityResizeAspectFill;
    [self.previewView.layer addSublayer:self.previewLayer];
    
    [self.captureSession startRunning];
    #endif
}

#if HAS_AVFF
- (void)captureOutput:(AVCaptureOutput *)captureOutput didOutputSampleBuffer:(CMSampleBufferRef)sampleBuffer fromConnection:(AVCaptureConnection *)connection { 
    if (!self.isDecoding) {
        return;
    }

    CVImageBufferRef imageBuffer = CMSampleBufferGetImageBuffer(sampleBuffer); 
    CVPixelBufferLockBaseAddress(imageBuffer, 0); 
    
    size_t bytesPerRow = CVPixelBufferGetBytesPerRow(imageBuffer); 
    size_t width = CVPixelBufferGetWidth(imageBuffer); 
    size_t height = CVPixelBufferGetHeight(imageBuffer); 
    
    uint8_t *baseAddress = (uint8_t *)CVPixelBufferGetBaseAddress(imageBuffer); 
    void *freeMe = 0;
    
    if (true) { // iOS bug?
        uint8_t *tmp = baseAddress;
        int bytes = bytesPerRow * height;
        freeMe = baseAddress = (uint8_t *)malloc(bytes);
        baseAddress[0] = 0xdb;
        memcpy(baseAddress, tmp, bytes);
    }
    
    CGColorSpaceRef colorSpace = CGColorSpaceCreateDeviceRGB(); 
    CGContextRef newContext = CGBitmapContextCreate(baseAddress, width, height, 8, bytesPerRow, colorSpace, kCGBitmapByteOrder32Little | kCGImageAlphaNoneSkipFirst); 
    
    CGImageRef capture = CGBitmapContextCreateImage(newContext); 
    CVPixelBufferUnlockBaseAddress(imageBuffer, 0);
    free(freeMe);
    
    CGContextRelease(newContext); 
    CGColorSpaceRelease(colorSpace);
    
    CGRect cropRect = [self.overlayView cropRect];
    
    // Won't work if the overlay becomes uncentered ...
    // iOS always takes videos in landscape
    // images are always 4x3; device is not
    // iOS uses virtual pixels for non-image stuff
    //
    // TODO improve crop calculation / coordinate stuff
    {
        CGFloat height = CGImageGetHeight(capture);
        CGFloat width = CGImageGetWidth(capture);
        
        cropRect.origin.x = (width - cropRect.size.width) / 2; // TODO: hardcoded - 20.0;
        cropRect.origin.y = (height - cropRect.size.height) / 2;
              
    }
    
    CGImageRef newImage = CGImageCreateWithImageInRect(capture, cropRect);
    CGImageRelease(capture);
    UIImage *screen = [[UIImage alloc] initWithCGImage:newImage];
    CGImageRelease(newImage);
    
    QRCodeReader *qrCodeReader = [[QRCodeReader alloc] init];
    Decoder *decoder = [[Decoder alloc] init];
    decoder.readers = [NSSet setWithObject:qrCodeReader];
    decoder.delegate = self;
    cropRect.origin.x = 0.0;
    cropRect.origin.y = 0.0;
    self.decoding = ![decoder decodeImage:screen cropRect:cropRect];
    [qrCodeReader release];
    [decoder release];
    [screen release];
} 
#endif

- (void)stopCapture {
    self.decoding = NO;
    
    #if HAS_AVFF
    [self.captureSession stopRunning];
    AVCaptureInput* input = [self.captureSession.inputs objectAtIndex:0];
    [self.captureSession removeInput:input];
    AVCaptureVideoDataOutput* output = (AVCaptureVideoDataOutput *)[self.captureSession.outputs objectAtIndex:0];
    [self.captureSession removeOutput:output];
    [self.previewLayer removeFromSuperlayer];
    self.previewLayer = nil;
    self.captureSession = nil;
    #endif
}


- (void)pushViewControllerForChallenge:(Challenge *)challenge {
    UIViewController *viewController = nil;
    if ([challenge isKindOfClass:[AuthenticationChallenge class]]) {
        AuthenticationChallenge *authenticationChallenge = (AuthenticationChallenge *)challenge;
        if (authenticationChallenge.identity == nil) {
            AuthenticationIdentityViewController *identityViewController = [[AuthenticationIdentityViewController alloc] initWithAuthenticationChallenge:authenticationChallenge];    
            identityViewController.managedObjectContext = self.managedObjectContext;
            viewController = identityViewController;
        } else {
            AuthenticationConfirmViewController *confirmViewController = [[AuthenticationConfirmViewController alloc] initWithAuthenticationChallenge:authenticationChallenge];
            confirmViewController.managedObjectContext = self.managedObjectContext;
            viewController = confirmViewController;
        } 
    } else {
        EnrollmentChallenge *enrollmentChallenge = (EnrollmentChallenge *)challenge;
        EnrollmentConfirmViewController *confirmViewController = [[EnrollmentConfirmViewController alloc] initWithEnrollmentChallenge:enrollmentChallenge]; 
        confirmViewController.managedObjectContext = self.managedObjectContext;
        viewController = confirmViewController;
    }
    
    [self.navigationController pushViewController:viewController animated:YES];
    
    [viewController release];
}

- (void)processChallenge:(NSString *)scanResult {
	dispatch_async(dispatch_get_global_queue(DISPATCH_QUEUE_PRIORITY_DEFAULT, 0), ^{
		dispatch_async(dispatch_get_main_queue(), ^{
			[MBProgressHUD showHUDAddedTo:self.navigationController.view animated:YES];
		});
        
        Challenge *challenge = nil;
        NSString *errorTitle = nil;
        NSString *errorMessage = nil;
        
        NSString *authenticationScheme = [[NSBundle mainBundle] objectForInfoDictionaryKey:@"TIQRAuthenticationURLScheme"]; 
        NSString *enrollmentScheme = [[NSBundle mainBundle] objectForInfoDictionaryKey:@"TIQREnrollmentURLScheme"]; 
        
        NSURL *url = [NSURL URLWithString:scanResult];
        if (url != nil && [url.scheme isEqualToString:authenticationScheme]) {
            challenge = [[AuthenticationChallenge alloc] initWithRawChallenge:scanResult managedObjectContext:self.managedObjectContext];
            errorTitle = challenge.isValid ? nil : [challenge.error localizedDescription];        
            errorMessage = challenge.isValid ? nil : [challenge.error localizedFailureReason];                
        } else if (url != nil && [url.scheme isEqualToString:enrollmentScheme]) {
            challenge = [[EnrollmentChallenge alloc] initWithRawChallenge:scanResult managedObjectContext:self.managedObjectContext];
            errorTitle = challenge.isValid ? nil : [challenge.error localizedDescription];        
            errorMessage = challenge.isValid ? nil : [challenge.error localizedFailureReason];                
        } else {
            errorTitle = NSLocalizedString(@"error_auth_invalid_qr_code", @"Invalid QR tag title");
            errorMessage = NSLocalizedString(@"error_auth_invalid_challenge_message", @"Unable to interpret the scanned QR tag. Please try again. If the problem persists, please contact the website adminstrator");
        }        
        
		dispatch_async(dispatch_get_main_queue(), ^{
			[MBProgressHUD hideHUDForView:self.navigationController.view animated:YES];
            
            if (challenge != nil && errorTitle == nil) {
                [self pushViewControllerForChallenge:challenge];
            } else {
                ErrorViewController *viewController = [[ErrorViewController alloc] initWithTitle:self.title errorTitle:errorTitle errorMessage:errorMessage];
                [self.navigationController pushViewController:viewController animated:YES];
                [viewController release];
            }            
		});
	});    
}

- (void)listIdentities {
    IdentityListViewController *viewController = [[IdentityListViewController alloc] init];
    viewController.managedObjectContext = self.managedObjectContext;
    [self.navigationController pushViewController:viewController animated:YES];
    [viewController release];
}

- (void)showInstructions {
    [self.navigationController popToRootViewControllerAnimated:YES];
}

- (void)resetOutlets {
    self.previewView = nil;
    self.instructionsView = nil;
    self.overlayView = nil;
    self.instructionLabel = nil;
}

- (void)viewDidUnload {
    [self resetOutlets];
    [super viewDidUnload];
}

- (void)dealloc {
    [self stopCapture];
    
    #if HAS_AVFF
    self.captureSession = nil;
    self.previewLayer = nil;
    #endif
    
    [self resetOutlets];
    
    self.managedObjectContext = nil;
    self.audioPlayer = nil;
    self.identitiesButtonItem = nil;
    
    [super dealloc];
}

@end