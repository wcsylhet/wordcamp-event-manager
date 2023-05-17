<template>
    <div class="qr_code_scanner">
        <el-button type="default" v-if="!cameras || !cameras.length" @click="initCamera()">With QR Code</el-button>
        <div v-loading="loading" class="camera_holder" id="cameraRender"></div>
        <el-select v-if="cameras && cameras.length" @change="startScanner()" placeholder="Select Camera" v-model="selectedCamera">
            <el-option
                v-for="item in cameras"
                :key="item.id"
                :label="item.label"
                :value="item.id">
            </el-option>
        </el-select>

        <el-checkbox v-if="selectedCamera" v-model="auto_checkin" true-label="yes" false-label="no">Enable Auto Checkin</el-checkbox>
    </div>
</template>

<script type="text/babel">
import {Html5Qrcode} from "html5-qrcode";
export default {
    name: 'QRCodeScanner',
    $emits: ['scanned'],
    props: ['loading'],
    data() {
        return {
            cameras: [],
            selectedCamera: null,
            selectCameraModal: false,
            deviceLoading: false,
            auto_checkin: 'no'
        }
    },
    methods: {
        initCamera() {
            this.deviceLoading = true;
            this.selectCameraModal = true;
            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length) {
                    this.cameras = devices
                }
                this.deviceLoading = false
            }).catch(err => {
                this.deviceLoading = false
            });
        },
        startScanner() {
            const html5QrCode = new Html5Qrcode('cameraRender');
            html5QrCode.start(
                this.selectedCamera,
                {
                    fps: 1,
                    qrbox: {width: 350, height: 350}
                },
                (decodedText, decodedResult) => {
                    this.emitScanSuccess(decodedText);
                },
                (errorMessage) => {
                    console.log('no..')
                })
                .catch((err) => {
                    // Start failed, handle it.
                });
        },
        emitScanSuccess(code) {
            this.$emit('scanned', code, this.auto_checkin);
        }
    }
}
</script>
