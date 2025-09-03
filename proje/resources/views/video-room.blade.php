<!DOCTYPE html>
<html>
<head>
    <title>Görüntülü Konuşma - Oda {{ $roomId }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <style>
        body {
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #1e4d3b 0%, #2d8659 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        .room-info {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            font-size: 14px;
        }
        .room-info h2 {
            color: #1e4d3b;
            margin: 0 0 5px 0;
            font-size: 18px;
        }
        .room-info p {
            color: #2d8659;
            margin: 0;
            font-size: 14px;
        }
        .video-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            max-width: 1400px;
            margin: 0 auto;
            margin-top: 30px;
        }
        .video-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .video-box h3 {
            margin: 0 0 15px 0;
            color: #1e4d3b;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
            font-weight: 500;
        }
        video {
            width: 100%;
            border-radius: 8px;
            background: #000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .controls {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 15px;
            background: rgba(255, 255, 255, 0.95);
            padding: 15px 25px;
            border-radius: 50px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
        }
        .control-button {
            padding: 12px;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            position: relative;
        }
        .control-button:hover {
            transform: translateY(-2px);
        }
        .start-button {
            background: #2d8659;
            color: white;
        }
        .camera-button {
            background: #2d8659;
            color: white;
        }
        .mic-button {
            background: #2d8659;
            color: white;
        }
        .control-button.off {
            background: #dc3545;
        }
        .leave-button {
            background: #dc3545;
            color: white;
        }
        .disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .tooltip {
            position: absolute;
            bottom: -30px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
        }
        .control-button:hover .tooltip {
            opacity: 1;
        }
    </style>
</head>
<body>
    <div class="room-info">
        <h2>Oda: {{ $roomId }}</h2>
        <p><i class="fas fa-user"></i> {{ $username }}</p>
    </div>

    <div class="video-grid">
        <div class="video-box">
            <h3><i class="fas fa-user-circle"></i> Sizin Görüntünüz</h3>
            <video id="localVideo" autoplay muted playsinline></video>
        </div>
        <div class="video-box">
            <h3><i class="fas fa-user-friends"></i> Karşı Taraf</h3>
            <video id="remoteVideo" autoplay playsinline></video>
        </div>
    </div>

    <div class="controls">
        <button id="startButton" class="control-button start-button" onclick="startCall()">
            <i class="fas fa-phone-alt"></i>
            <span class="tooltip">Görüşmeyi Başlat</span>
        </button>
        <button id="cameraButton" class="control-button camera-button" onclick="toggleCamera()">
            <i class="fas fa-video"></i>
            <span class="tooltip">Kamerayı Aç/Kapat</span>
        </button>
        <button id="micButton" class="control-button mic-button" onclick="toggleMic()">
            <i class="fas fa-microphone"></i>
            <span class="tooltip">Mikrofonu Aç/Kapat</span>
        </button>
        <button id="leaveButton" class="control-button leave-button" onclick="leaveRoom()">
            <i class="fas fa-phone-slash"></i>
            <span class="tooltip">Görüşmeden Ayrıl</span>
        </button>
    </div>

    <script>
        const roomId = "{{ $roomId }}";
        const username = "{{ $username }}";
        let peerConnection;
        let localStream;
        
        // Pusher bağlantısını güncelleyelim
        const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
            forceTLS: true,
            authEndpoint: '/broadcasting/auth',
            auth: {
                params: {
                    username: username,
                    room_id: roomId
                }
            }
        });

        // Channel'a abone olalım
        const channelName = `presence-video-room-${roomId}`;
        let channel;

        // Görüşme başlatma fonksiyonu
        async function startCall() {
            if (!channel || !channel.subscribed) {
                console.log('Kanal henüz hazır değil, bekleniyor...');
                setTimeout(startCall, 1000);
                return;
            }

            await initializePeerConnection();
            document.getElementById('startButton').disabled = true;
        }

        // Kamera başlatma fonksiyonu
        async function initializeCamera() {
            try {
                localStream = await navigator.mediaDevices.getUserMedia({
                    video: true,
                    audio: true
                });
                document.getElementById('localVideo').srcObject = localStream;
                return true;
            } catch (error) {
                console.error('Kamera başlatma hatası:', error);
                alert('Kamera erişimi sağlanamadı!');
                return false;
            }
        }

        // Pusher bağlantı olayları
        pusher.connection.bind('connected', async () => {
            console.log('Pusher bağlantısı başarılı!', pusher.connection.socket_id);
            
            // Önce kamerayı başlat
            const cameraInitialized = await initializeCamera();
            if (!cameraInitialized) return;

            // Sonra channel'a abone ol
            channel = pusher.subscribe(channelName);
            
            channel.bind('pusher:subscription_succeeded', (members) => {
                console.log('Kanala başarıyla abone olundu!', members);
                if(members.count > 1) {
                    startCall();
                }
            });

            channel.bind('pusher:member_added', (member) => {
                console.log('Yeni kullanıcı katıldı:', member.info);
                startCall();
            });

            channel.bind('pusher:member_removed', (member) => {
                console.log('Kullanıcı ayrıldı:', member.info);
                document.getElementById('remoteVideo').srcObject = null;
                alert('Diğer kullanıcı görüşmeden ayrıldı');
            });

            channel.bind('pusher:subscription_error', (error) => {
                console.error('Kanal abonelik hatası:', error);
                // 3 saniye sonra yeniden bağlanmayı dene
                setTimeout(() => {
                    channel = pusher.subscribe(channelName);
                }, 3000);
            });

            // Event'leri bağla
            bindChannelEvents();
        });

        function bindChannelEvents() {
            channel.bind('client-offer', async (data) => {
                console.log('Offer alındı');
                await handleOffer(data.offer);
            });

            channel.bind('client-answer', async (data) => {
                console.log('Answer alındı');
                await handleAnswer(data.answer);
            });

            channel.bind('client-ice-candidate', async (data) => {
                console.log('ICE candidate alındı');
                await handleIceCandidate(data.candidate);
            });
        }

        // Peer Connection başlatma
        async function initializePeerConnection() {
            if (!localStream) {
                console.error('Yerel stream hazır değil!');
                return;
            }

            if (peerConnection) {
                peerConnection.close();
            }

            peerConnection = new RTCPeerConnection({
                iceServers: [
                    { urls: 'stun:stun.l.google.com:19302' },
                    { urls: 'stun:stun1.l.google.com:19302' }
                ]
            });

            localStream.getTracks().forEach(track => {
                peerConnection.addTrack(track, localStream);
            });

            peerConnection.ontrack = event => {
                console.log('Remote track alındı');
                document.getElementById('remoteVideo').srcObject = event.streams[0];
            };

            peerConnection.onicecandidate = event => {
                if (event.candidate && channel && channel.subscribed) {
                    console.log('ICE candidate gönderiliyor');
                    channel.trigger('client-ice-candidate', {
                        candidate: event.candidate
                    });
                }
            };

            try {
                const offer = await peerConnection.createOffer();
                await peerConnection.setLocalDescription(offer);
                
                if (channel && channel.subscribed) {
                    console.log('Offer gönderiliyor');
                    channel.trigger('client-offer', { offer });
                }
            } catch (error) {
                console.error('Offer oluşturma hatası:', error);
            }
        }

        async function handleOffer(offer) {
            try {
                if (!peerConnection) {
                    await initializePeerConnection();
                }
                
                await peerConnection.setRemoteDescription(new RTCSessionDescription(offer));
                const answer = await peerConnection.createAnswer();
                await peerConnection.setLocalDescription(answer);
                
                channel.trigger('client-answer', {
                    answer: answer
                });
            } catch (error) {
                console.error('Offer işleme hatası:', error);
            }
        }

        async function handleAnswer(answer) {
            try {
                await peerConnection.setRemoteDescription(new RTCSessionDescription(answer));
            } catch (error) {
                console.error('Answer işleme hatası:', error);
            }
        }

        async function handleIceCandidate(candidate) {
            try {
                if (peerConnection) {
                    await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                }
            } catch (error) {
                console.error('ICE Candidate hatası:', error);
            }
        }

        function leaveRoom() {
            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
            }
            if (peerConnection) {
                peerConnection.close();
            }
            window.location.href = '/';
        }

        window.onbeforeunload = leaveRoom;

        let isCameraOn = true;
        let isMicOn = true;

        function toggleCamera() {
            if (localStream) {
                const videoTrack = localStream.getVideoTracks()[0];
                if (videoTrack) {
                    isCameraOn = !isCameraOn;
                    videoTrack.enabled = isCameraOn;
                    document.querySelector('#cameraButton i').className = 
                        isCameraOn ? 'fas fa-video' : 'fas fa-video-slash';
                    document.querySelector('#cameraButton').classList.toggle('off', !isCameraOn);
                }
            }
        }

        function toggleMic() {
            if (localStream) {
                const audioTrack = localStream.getAudioTracks()[0];
                if (audioTrack) {
                    isMicOn = !isMicOn;
                    audioTrack.enabled = isMicOn;
                    document.querySelector('#micButton i').className = 
                        isMicOn ? 'fas fa-microphone' : 'fas fa-microphone-slash';
                    document.querySelector('#micButton').classList.toggle('off', !isMicOn);
                }
            }
        }
    </script>
</body>
</html> 