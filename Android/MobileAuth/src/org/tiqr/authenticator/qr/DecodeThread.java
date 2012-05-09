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

package org.tiqr.authenticator.qr;

import com.google.zxing.BarcodeFormat;
import com.google.zxing.DecodeHintType;
import com.google.zxing.ResultPointCallback;

import android.os.Handler;
import android.os.Looper;

import java.util.Hashtable;
import java.util.Vector;
import java.util.concurrent.CountDownLatch;

/**
 * This thread does all the heavy lifting of decoding the images.
 * 
 * @author dswitkin@google.com (Daniel Switkin)
 */
final class DecodeThread extends Thread
{
    public static final String BARCODE_BITMAP = "barcode_bitmap";

    private final CaptureActivity _activity;
    private final Hashtable<DecodeHintType, Object> _hints;
    private Handler _handler;
    private final CountDownLatch _handlerInitLatch;

    DecodeThread(CaptureActivity activity, ResultPointCallback resultPointCallback)
    {
        _activity = activity;
        _handlerInitLatch = new CountDownLatch(1);

        _hints = new Hashtable<DecodeHintType, Object>(3);
        Vector<BarcodeFormat> decodeFormats = new Vector<BarcodeFormat>();
        decodeFormats.add(BarcodeFormat.QR_CODE);
        _hints.put(DecodeHintType.POSSIBLE_FORMATS, decodeFormats);
        _hints.put(DecodeHintType.NEED_RESULT_POINT_CALLBACK, resultPointCallback);
    }

    Handler getHandler()
    {
        try {
            _handlerInitLatch.await();
        } catch (InterruptedException ie) {
            // continue?
        }
        
        return _handler;
    }

    @Override
    public void run()
    {
        Looper.prepare();
        _handler = new DecodeHandler(_activity, _hints);
        _handlerInitLatch.countDown();
        Looper.loop();
    }

}