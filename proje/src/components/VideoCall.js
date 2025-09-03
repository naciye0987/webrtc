import React, { useEffect, useRef, useState } from 'react';
import { pusherClient, videoCallChannel } from '../utils/pusherConfig';

const VideoCall = ({ userId, targetUserId }) => {
  const localVideoRef = useRef(null);
  const remoteVideoRef = useRef(null);
  const [peerConnection, setPeerConnection] = useState(null);

  useEffect(() => {
    // Yerel video akışını başlat
    navigator.mediaDevices.getUserMedia({ video: true, audio: true })
      .then(stream => {
        if (localVideoRef.current) {
          localVideoRef.current.srcObject = stream;
        }
        
        const pc = new RTCPeerConnection({
          iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
        });
        
        stream.getTracks().forEach(track => {
          pc.addTrack(track, stream);
        });
        
        setPeerConnection(pc);
        
        // Uzak video akışını dinle
        pc.ontrack = (event) => {
          if (remoteVideoRef.current) {
            remoteVideoRef.current.srcObject = event.streams[0];
          }
        };
      });

    // Pusher event dinleyicileri
    videoCallChannel.bind('client-offer', handleOffer);
    videoCallChannel.bind('client-answer', handleAnswer);
    videoCallChannel.bind('client-ice-candidate', handleIceCandidate);

    return () => {
      videoCallChannel.unbind_all();
      if (peerConnection) {
        peerConnection.close();
      }
    };
  }, []);

  // Gerekli işleyici fonksiyonları
  const handleOffer = async (offer) => {
    await peerConnection.setRemoteDescription(new RTCSessionDescription(offer));
    const answer = await peerConnection.createAnswer();
    await peerConnection.setLocalDescription(answer);
    
    videoCallChannel.trigger('client-answer', {
      answer,
      targetUserId
    });
  };

  const handleAnswer = async (answer) => {
    await peerConnection.setRemoteDescription(new RTCSessionDescription(answer));
  };

  const handleIceCandidate = async (candidate) => {
    await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
  };

  return (
    <div className="video-call-container">
      <video ref={localVideoRef} autoPlay muted playsInline className="local-video" />
      <video ref={remoteVideoRef} autoPlay playsInline className="remote-video" />
    </div>
  );
};

export default VideoCall; 