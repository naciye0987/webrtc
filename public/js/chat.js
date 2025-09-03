let localStream;
let remoteStream;
let peerConnection;

const configuration = {
    iceServers: [
        { urls: 'stun:stun.l.google.com:19302' }
    ]
};

// Pusher ayarları
const pusher = new Pusher('9beae973cadd89f3a2db', {
    cluster: 'mt1',
    encrypted: true
});

const channel = pusher.subscribe(`presence-video-channel-${roomId}`);

async function startVideo() {
    try {
        localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        document.getElementById('localVideo').srcObject = localStream;

        setupPeerConnection();
    } catch (error) {
        console.error('Error accessing media devices:', error);
    }
}

function setupPeerConnection() {
    peerConnection = new RTCPeerConnection(configuration);

    localStream.getTracks().forEach(track => {
        peerConnection.addTrack(track, localStream);
    });

    peerConnection.ontrack = event => {
        document.getElementById('remoteVideo').srcObject = event.streams[0];
    };

    peerConnection.onicecandidate = event => {
        if (event.candidate) {
            channel.trigger('client-ice-candidate', {
                candidate: event.candidate,
                roomId: roomId
            });
        }
    };
}

channel.bind('client-offer', async data => {
    if (data.roomId === roomId) {
        await peerConnection.setRemoteDescription(new RTCSessionDescription(data.offer));
        const answer = await peerConnection.createAnswer();
        await peerConnection.setLocalDescription(answer);

        channel.trigger('client-answer', {
            answer: answer,
            roomId: roomId
        });
    }
});

channel.bind('client-answer', async data => {
    if (data.roomId === roomId) {
        await peerConnection.setRemoteDescription(new RTCSessionDescription(data.answer));
    }
});

channel.bind('client-ice-candidate', data => {
    if (data.roomId === roomId) {
        peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
    }
});

startVideo(); 