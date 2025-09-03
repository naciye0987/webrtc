<!DOCTYPE html>
<html>
<head>
    <title>Görüntülü Konuşma</title>
    <link href="{{ asset('css/VideoCall.css') }}" rel="stylesheet">
</head>
<body>
    <div id="video-container">
        <button onclick="startCall()" id="callButton">Aramayı Başlat</button>
        <div class="video-call-container">
            <video id="localVideo" autoplay muted playsinline></video>
            <video id="remoteVideo" autoplay playsinline></video>
        </div>
    </div>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        // Pusher bağlantısı
        const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
            encrypted: true
        });

        const channel = pusher.subscribe('video-call');
        let peerConnection;
        let localStream;

        async function startCall() {
            try {
                // Kamera ve mikrofon erişimi
                localStream = await navigator.mediaDevices.getUserMedia({
                    video: true,
                    audio: true
                });
                
                document.getElementById('localVideo').srcObject = localStream;

                // WebRTC bağlantısı
                peerConnection = new RTCPeerConnection({
                    iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
                });

                // Yerel video akışını peer connection'a ekle
                localStream.getTracks().forEach(track => {
                    peerConnection.addTrack(track, localStream);
                });

                // Offer oluştur
                const offer = await peerConnection.createOffer();
                await peerConnection.setLocalDescription(offer);

                // Offer'ı karşı tarafa gönder
                channel.trigger('client-offer', {
                    offer: offer
                });

                // ICE adaylarını dinle ve gönder
                peerConnection.onicecandidate = (event) => {
                    if (event.candidate) {
                        channel.trigger('client-ice-candidate', {
                            candidate: event.candidate
                        });
                    }
                };

                // Uzak video akışını al
                peerConnection.ontrack = (event) => {
                    document.getElementById('remoteVideo').srcObject = event.streams[0];
                };
            } catch (error) {
                console.error('Hata:', error);
                alert('Kamera ve mikrofon erişimi sağlanamadı!');
            }
        }

        // Gelen offer'ı dinle
        channel.bind('client-offer', async (data) => {
            try {
                if (!peerConnection) {
                    localStream = await navigator.mediaDevices.getUserMedia({
                        video: true,
                        audio: true
                    });
                    document.getElementById('localVideo').srcObject = localStream;

                    peerConnection = new RTCPeerConnection({
                        iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
                    });

                    localStream.getTracks().forEach(track => {
                        peerConnection.addTrack(track, localStream);
                    });
                }

                await peerConnection.setRemoteDescription(new RTCSessionDescription(data.offer));
                const answer = await peerConnection.createAnswer();
                await peerConnection.setLocalDescription(answer);

                channel.trigger('client-answer', {
                    answer: answer
                });
            } catch (error) {
                console.error('Hata:', error);
            }
        });

        // Gelen answer'ı dinle
        channel.bind('client-answer', async (data) => {
            try {
                await peerConnection.setRemoteDescription(new RTCSessionDescription(data.answer));
            } catch (error) {
                console.error('Hata:', error);
            }
        });

        // ICE adaylarını dinle
        channel.bind('client-ice-candidate', async (data) => {
            try {
                await peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
            } catch (error) {
                console.error('Hata:', error);
            }
        });
    </script>
</body>
</html>
