/*
 * Copyright (C) 2008 ZXing authors
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

package org.tiqr.authenticator.qr.camera;

import java.io.IOException;

import org.tiqr.authenticator.qr.PlanarYUVLuminanceSource;
import android.content.Context;
import android.graphics.PixelFormat;
import android.graphics.Point;
import android.graphics.Rect;
import android.hardware.Camera;
import android.os.Handler;
import android.util.Log;
import android.view.SurfaceHolder;

/**
 * This object wraps the Camera service object and expects to be the only one
 * talking to it. The implementation encapsulates the steps needed to take
 * preview-sized images, which are used for both preview and decoding.
 * 
 * @author dswitkin@google.com (Daniel Switkin)
 */
public final class CameraManager
{

    private static final String TAG = CameraManager.class.getSimpleName();

    private static final int MIN_FRAME_WIDTH = 240;
    private static final int MIN_FRAME_HEIGHT = 240;
    private static final int MAX_FRAME_WIDTH = 360;
    private static final int MAX_FRAME_HEIGHT = 360;

    private static CameraManager cameraManager;

    private final CameraConfigurationManager _configManager;
    private Camera _camera;
    private Rect _framingRect;
    private Rect _framingRectInPreview;
    private boolean _initialized;
    private boolean _previewing;

    /**
     * Preview frames are delivered here, which we pass on to the registered
     * handler. Make sure to clear the handler so it will only receive one
     * message.
     */
    private final PreviewCallback previewCallback;
    
    /**
     * Autofocus callbacks arrive here, and are dispatched to the Handler which
     * requested them.
     */
    private final AutoFocusCallback autoFocusCallback;

    /**
     * Initializes this static object with the Context of the calling Activity.
     * 
     * @param context
     *            The Activity which wants to use the _camera.
     */
    public static void init(Context context)
    {
        if (cameraManager == null) {
            cameraManager = new CameraManager(context);
        }
    }

    /**
     * Gets the CameraManager singleton instance.
     * 
     * @return A reference to the CameraManager singleton.
     */
    public static CameraManager get()
    {
        return cameraManager;
    }

    private CameraManager(Context context)
    {
        this._configManager = new CameraConfigurationManager();
        previewCallback = new PreviewCallback(_configManager);
        autoFocusCallback = new AutoFocusCallback();
    }

    /**
     * Opens the _camera driver and initializes the hardware parameters.
     * 
     * @param holder
     *            The surface object which the _camera will draw preview frames
     *            into.
     * @throws IOException
     *             Indicates the _camera driver failed to open.
     */
    public void openDriver(SurfaceHolder holder) throws IOException
    {
        if (_camera != null) {
            return;
        }
        
        _camera = Camera.open();
        if (_camera == null) {
            throw new IOException();
        }
        
        _camera.setPreviewDisplay(holder);

        if (!_initialized) {
            _initialized = true;
            _configManager.init(_camera, holder);
        }
        
        _configManager.setDesiredCameraParameters(_camera);
    }

    /**
     * Closes the _camera driver if still in use.
     */
    public void closeDriver()
    {
        if (_camera != null) {
            _camera.release();
            _camera = null;
        }
    }

    /**
     * Asks the _camera hardware to begin drawing preview frames to the screen.
     */
    public void startPreview()
    {
        if (_camera != null && !_previewing) {
            _camera.startPreview();
            _previewing = true;
        }
    }

    /**
     * Tells the _camera to stop drawing preview frames.
     */
    public void stopPreview()
    {
        if (_camera != null && _previewing) {
            _camera.stopPreview();
            previewCallback.setHandler(null, 0);
            autoFocusCallback.setHandler(null, 0);
            _previewing = false;
        }
    }

    /**
     * A single preview frame will be returned to the handler supplied. The data
     * will arrive as byte[] in the message.obj field, with width and height
     * encoded as message.arg1 and message.arg2, respectively.
     * 
     * @param handler
     *            The handler to send the message to.
     * @param message
     *            The what field of the message to be sent.
     */
    public void requestPreviewFrame(Handler handler, int message)
    {
        if (_camera != null && _previewing) {
            previewCallback.setHandler(handler, message);
            _camera.setOneShotPreviewCallback(previewCallback);
        }
    }

    /**
     * Asks the _camera hardware to perform an autofocus.
     * 
     * @param handler
     *            The Handler to notify when the autofocus completes.
     * @param message
     *            The message to deliver.
     */
    public void requestAutoFocus(Handler handler, int message)
    {
        if (_camera != null && _previewing) {
            autoFocusCallback.setHandler(handler, message);
            _camera.autoFocus(autoFocusCallback);
        }
    }

    /**
     * Calculates the framing rect which the UI should draw to show the user
     * where to place the barcode. This target helps with alignment as well as
     * forces the user to hold the device far enough away to ensure the image
     * will be in focus.
     * 
     * @return The rectangle to draw on screen in window coordinates.
     */
    public Rect getFramingRect()
    {
        if (_framingRect != null) {
            return _framingRect;
        }
        
        if (_camera == null) {
            return null;
        }

        Point surfaceResolution = _configManager.getSurfaceResolution();

        int width = surfaceResolution.x * 3 / 4;
        if (width < MIN_FRAME_WIDTH) {
            width = MIN_FRAME_WIDTH;
        } else if (width > MAX_FRAME_WIDTH) {
            width = MAX_FRAME_WIDTH;
        }
        
        int height = surfaceResolution.y * 3 / 4;
        if (height < MIN_FRAME_HEIGHT) {
            height = MIN_FRAME_HEIGHT;
        } else if (height > MAX_FRAME_HEIGHT) {
            height = MAX_FRAME_HEIGHT;
        }
        int leftOffset = (surfaceResolution.x - width) / 2;
        int topOffset = (surfaceResolution.y - height) / 2;
        _framingRect = new Rect(leftOffset, topOffset, leftOffset + width, topOffset + height);
        Log.d(TAG, "Calculated framing rect: " + _framingRect);
        
        return _framingRect;
    }

    /**
     * Like {@link #getFramingRect} but coordinates are in terms of the preview
     * frame, not UI / screen.
     */
    public Rect getFramingRectInPreview()
    {
        if (_framingRectInPreview == null) {
            Rect rect = new Rect(getFramingRect());
            
            Point cameraResolution = _configManager.getCameraResolution();
            Point surfaceResolution = _configManager.getSurfaceResolution();
            
            // HACK: x and y get swapped here because the frame is rotated even though the device is rotated. 
            rect.left = rect.left * cameraResolution.y / surfaceResolution.x;
            rect.right = rect.right * cameraResolution.y / surfaceResolution.x;
            rect.top = rect.top * cameraResolution.x / surfaceResolution.y;
            rect.bottom = rect.bottom * cameraResolution.x / surfaceResolution.y;

            _framingRectInPreview = rect;
        }
        return _framingRectInPreview;
    }

    /**
     * A factory method to build the appropriate LuminanceSource object based on
     * the format of the preview buffers, as described by Camera.Parameters.
     * 
     * @param data
     *            A preview frame.
     * @param width
     *            The width of the image.
     * @param height
     *            The height of the image.
     * @return A PlanarYUVLuminanceSource instance.
     */
    public PlanarYUVLuminanceSource buildLuminanceSource(byte[] data, int width, int height)
    {
        Rect rect = getFramingRectInPreview();
        int previewFormat = _configManager.getPreviewFormat();
        String previewFormatString = _configManager.getPreviewFormatString();
        boolean reverseHorizontal = false;

        //Log.d(TAG, "rect: " + rect + " fmt string: " +previewFormatString);
        
        switch (previewFormat) {
        // This is the standard Android format which all devices are REQUIRED to
        // support.
        // In theory, it's the only one we should ever care about.
        case PixelFormat.YCbCr_420_SP:
            // This format has never been seen in the wild, but is compatible as
            // we only care
            // about the Y channel, so allow it.
        case PixelFormat.YCbCr_422_SP:
            return new PlanarYUVLuminanceSource(data, width, height, rect.left, rect.top, rect.width(), rect.height(), reverseHorizontal);
        default:
            // The Samsung Moment incorrectly uses this variant instead of the
            // 'sp' version.
            // Fortunately, it too has all the Y data up front, so we can read
            // it.
            if ("yuv420p".equals(previewFormatString)) {
                return new PlanarYUVLuminanceSource(data, width, height, rect.left, rect.top, rect.width(), rect.height(), reverseHorizontal);
            }
        }
        throw new IllegalArgumentException("Unsupported picture format: " + previewFormat + '/' + previewFormatString);
    }

}
