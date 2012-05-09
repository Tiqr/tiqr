/*
 * Copyright (C) 2010 ZXing authors
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

import java.lang.reflect.Method;
import java.util.regex.Pattern;

import android.graphics.Point;
import android.hardware.Camera;
import android.os.Build;
import android.util.Log;
import android.view.SurfaceHolder;

/**
 * Camera configuration manager.
 */
final class CameraConfigurationManager
{
    private static final String TAG = CameraConfigurationManager.class.getSimpleName();
    private static final Pattern COMMA_PATTERN = Pattern.compile(",");

    private Point _surfaceResolution;
    private Point _cameraResolution;
    private int _previewFormat;
    private String _previewFormatString;

    /**
     * Calculates the optimal preview size
     * 
     * @param previewSizeValueString
     * @param surfaceResolution
     * 
     * @return optimal preview size
     */
    private static Point _calculateOptimalPreviewSize(CharSequence previewSizeValueString, Point surfaceResolution)
    {
        int bestX = 0;
        int bestY = 0;
        int diff = Integer.MAX_VALUE;
        for (String previewSize : COMMA_PATTERN.split(previewSizeValueString)) {
            previewSize = previewSize.trim();
            int dimPosition = previewSize.indexOf('x');
            if (dimPosition < 0) {
                Log.w(TAG, "Bad preview-size: " + previewSize);
                continue;
            }

            int newX;
            int newY;
            try {
                newX = Integer.parseInt(previewSize.substring(0, dimPosition));
                newY = Integer.parseInt(previewSize.substring(dimPosition + 1));
            } catch (NumberFormatException nfe) {
                Log.w(TAG, "Bad preview-size: " + previewSize);
                continue;
            }

            int newDiff = Math.abs(newX - surfaceResolution.x) + Math.abs(newY - surfaceResolution.y);
            if (newDiff == 0) {
                bestX = newX;
                bestY = newY;
                break;
            } else if (newDiff < diff) {
                bestX = newX;
                bestY = newY;
                diff = newDiff;
            }

        }

        if (bestX > 0 && bestY > 0) {
            return new Point(bestX, bestY);
        }
        
        return null;
    }       
    

    /**
     * Calculates the optimal camera resolution.
     * 
     * @param parameters        camera parameters
     * @param surfaceResolution surface resolution
     * @return
     */
    private static Point _calculateOptimalCameraResolution(Camera.Parameters parameters, Point surfaceResolution)
    {
        String previewSizeValueString = parameters.get("preview-size-values");
        if (previewSizeValueString == null) {
            previewSizeValueString = parameters.get("preview-size-value");
        }

        Point cameraResolution = null;

        if (previewSizeValueString != null) {
            Log.d(TAG, "preview-size-values parameter: " + previewSizeValueString);
            cameraResolution = _calculateOptimalPreviewSize(previewSizeValueString, surfaceResolution);
        }

        if (cameraResolution == null) {
            // Ensure that the camera resolution is a multiple of 8, as the surface may not be.
            cameraResolution = new Point((surfaceResolution.x >> 3) << 3, (surfaceResolution.y >> 3) << 3);
        }

        return cameraResolution;
    } 

    /**
     * Reads, one time, values from the camera that are needed by the app.
     */
    void init(Camera camera, SurfaceHolder surfaceHolder)
    {
        Camera.Parameters parameters = camera.getParameters();
        _previewFormat = parameters.getPreviewFormat();
        _previewFormatString = parameters.get("preview-format");
        Log.d(TAG, "Default preview format: " + _previewFormat + '/' + _previewFormatString);
        _surfaceResolution = new Point(surfaceHolder.getSurfaceFrame().width(), surfaceHolder.getSurfaceFrame().height());
        Log.d(TAG, "Surface resolution: " + _surfaceResolution);
        _cameraResolution = _calculateOptimalCameraResolution(parameters, _surfaceResolution);
        Log.d(TAG, "Camera resolution: " + _cameraResolution);
    }

    /**
     * Sets the camera up to take preview images which are used for both preview
     * and decoding. We detect the preview format here so that
     * buildLuminanceSource() can build an appropriate LuminanceSource subclass.
     * In the future we may want to force YUV420SP as it's the smallest, and the
     * planar Y can be used for barcode scanning without a copy in some cases.
     */
    public void setDesiredCameraParameters(Camera camera)
    {
        // TODO: find a way to do this without falling back to reflection
        boolean usePortraitOrientation = Integer.parseInt(Build.VERSION.SDK) >= 8;
        if (usePortraitOrientation) { // only 2.2
            try {
                Method method = camera.getClass().getMethod("setDisplayOrientation", new Class[] { int.class });
                method.invoke(camera, 90);
            } catch (Exception ex) {

            }
        }   
        
        Camera.Parameters parameters = camera.getParameters();
        parameters.setPreviewSize(_cameraResolution.x, _cameraResolution.y);
        parameters.set("orientation", "portrait");
        parameters.set("rotation", 90);
        camera.setParameters(parameters);
        
        Log.d(TAG, "Set camera preview size: " + parameters.getPreviewSize());
    }

    /**
     * Returns the camera resolution used.
     * 
     * @return camera resolution
     */
    public Point getCameraResolution()
    {
        return _cameraResolution;
    }

    /**
     * Returns the surface resolution.
     * 
     * @return surface resolution
     */
    public Point getSurfaceResolution()
    {
        return _surfaceResolution;
    }

    /**
     * Returns the preview format.
     */
    public int getPreviewFormat()
    {
        return _previewFormat;
    }

    /**
     * Returns the preview format string.
     */
    public String getPreviewFormatString()
    {
        return _previewFormatString;
    }
}