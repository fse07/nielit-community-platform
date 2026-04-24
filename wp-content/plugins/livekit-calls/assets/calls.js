(function($){
  if (typeof LivekitClient === 'undefined') { console.warn('LiveKit not loaded'); return; }

  let room = null, localTracks = {}, screenTrack = null;
  let currentCallId = null;
  let ringingForCallId = null;

  function openModal(title) {
    $('#lkc-title').text(title);
    $('#lkc-status').text('Connecting…');
    $('#lkc-videos').empty();
    $('#lkc-modal').show();
  }
  function closeModal() {
    $('#lkc-modal').hide();
    $('#lkc-videos').empty();
  }

  async function connectToRoom(wsUrl, token, mode) {
    room = new LivekitClient.Room({ adaptiveStream: true, dynacast: true });

    room.on(LivekitClient.RoomEvent.ParticipantConnected, p =>
      $('#lkc-status').text(p.identity + ' joined'));
    room.on(LivekitClient.RoomEvent.TrackSubscribed, (track) => {
      const el = track.attach();
      el.classList.add('lkc-video','lkc-remote');
      el.autoplay = true; el.playsInline = true;
      $('#lkc-videos').append(el);
    });
    room.on(LivekitClient.RoomEvent.Disconnected, () => closeModal());

    await room.connect(wsUrl, token);
    $('#lkc-status').text('Waiting for other user…');

    const mic = await LivekitClient.createLocalAudioTrack();
    await room.localParticipant.publishTrack(mic);
    localTracks.mic = mic;

    if (mode === 'video') {
      const cam = await LivekitClient.createLocalVideoTrack();
      await room.localParticipant.publishTrack(cam);
      localTracks.cam = cam;
      const el = cam.attach();
      el.muted = true;
      el.classList.add('lkc-video','lkc-local');
      $('#lkc-videos').append(el);
    } else {
      // Audio-only — show a placeholder so screen isn't blank
      $('#lkc-videos').append('<div class="lkc-audio-placeholder">🎙️ Audio call in progress</div>');
    }
  }

  async function startCall(recipientId, mode) {
    openModal(mode === 'video' ? 'Video call' : 'Audio call');
    $('#lkc-status').text('Ringing…');
    try {
      const resp = await $.post(LKC.ajax_url, {
        action: 'lkc_start_call',
        nonce: LKC.nonce,
        recipient_id: recipientId,
        mode: mode,
      });
      if (!resp.success) { alert('Failed: '+resp.data.message); closeModal(); return; }
      currentCallId = resp.data.call_id;
      await connectToRoom(resp.data.ws_url, resp.data.token, mode);
    } catch (e) {
      console.error(e); alert('Call failed: '+e.message); closeModal();
    }
  }

  async function acceptCall(callId) {
    stopRinging();
    $('#lkc-incoming').hide();
    try {
      const resp = await $.post(LKC.ajax_url, {
        action: 'lkc_accept_call',
        nonce: LKC.nonce,
        call_id: callId,
      });
      if (!resp.success) { alert('Failed: '+resp.data.message); return; }
      currentCallId = callId;
      openModal(resp.data.mode === 'video' ? 'Video call' : 'Audio call');
      await connectToRoom(resp.data.ws_url, resp.data.token, resp.data.mode);
    } catch (e) {
      console.error(e); alert('Accept failed: '+e.message);
    }
  }

  function declineCall(callId) {
    stopRinging();
    $('#lkc-incoming').hide();
    $.post(LKC.ajax_url, { action: 'lkc_end_call', nonce: LKC.nonce, call_id: callId });
  }

  async function toggleMic() {
    if (!localTracks.mic) return;
    localTracks.mic.isMuted ? await localTracks.mic.unmute() : await localTracks.mic.mute();
    $('#lkc-mic').text(localTracks.mic.isMuted ? '🔇' : '🎤');
  }
  async function toggleCam() {
    if (!room) return;
    if (localTracks.cam) {
      await room.localParticipant.unpublishTrack(localTracks.cam);
      localTracks.cam.stop(); localTracks.cam = null;
      $('#lkc-cam').text('📵');
    } else {
      const cam = await LivekitClient.createLocalVideoTrack();
      await room.localParticipant.publishTrack(cam);
      localTracks.cam = cam;
      const el = cam.attach();
      el.muted = true; el.classList.add('lkc-video','lkc-local');
      $('#lkc-videos').append(el);
      $('#lkc-cam').text('📷');
    }
  }
  async function toggleScreen() {
    if (!room) return;
    if (screenTrack) {
      await room.localParticipant.unpublishTrack(screenTrack);
      screenTrack.stop(); screenTrack = null;
      $('#lkc-screen').text('🖥️');
    } else {
      const tracks = await LivekitClient.createLocalScreenTracks({ audio: true });
      for (const t of tracks) await room.localParticipant.publishTrack(t);
      screenTrack = tracks[0];
      $('#lkc-screen').text('🛑');
    }
  }
  async function hangUp() {
    if (currentCallId) {
      $.post(LKC.ajax_url, { action: 'lkc_end_call', nonce: LKC.nonce, call_id: currentCallId });
    }
    if (room) await room.disconnect();
    room = null; localTracks = {}; screenTrack = null; currentCallId = null;
    closeModal();
  }

  // Ringing sound (uses Web Audio API — no external file needed)
  let ringInterval = null, audioCtx = null;
  function startRinging() {
    if (ringInterval) return;
    try {
      audioCtx = new (window.AudioContext || window.webkitAudioContext)();
      const beep = () => {
        const o = audioCtx.createOscillator();
        const g = audioCtx.createGain();
        o.connect(g); g.connect(audioCtx.destination);
        o.frequency.value = 440; g.gain.value = 0.15;
        o.start(); o.stop(audioCtx.currentTime + 0.3);
      };
      beep();
      ringInterval = setInterval(beep, 1200);
    } catch(e) { console.warn('Audio blocked:', e); }
  }
  function stopRinging() {
    if (ringInterval) { clearInterval(ringInterval); ringInterval = null; }
    if (audioCtx) { try { audioCtx.close(); } catch(e){} audioCtx = null; }
  }

  // Listen for incoming calls via WordPress heartbeat
  $(document).on('heartbeat-tick', function(e, data) {
    if (!data.lkc_incoming || data.lkc_incoming.length === 0) {
      if (ringingForCallId) {
        stopRinging();
        $('#lkc-incoming').hide();
        ringingForCallId = null;
      }
      return;
    }
    const call = data.lkc_incoming[0];
    if (ringingForCallId === parseInt(call.id)) return; // already ringing for this
    if (room) return; // user already on a call — ignore

    ringingForCallId = parseInt(call.id);
    $('#lkc-ring-from').text(
      call.caller_name + ' is calling ' + (call.call_mode === 'video' ? '(Video)' : '(Audio)')
    );
    $('#lkc-accept').data('call-id', call.id);
    $('#lkc-decline').data('call-id', call.id);
    $('#lkc-incoming').show();
    startRinging();
  });

  // Event handlers
  $(document).on('click', '.lkc-start', function(e){
    e.preventDefault();
    startCall($(this).data('recipient'), $(this).data('mode'));
  });
  $(document).on('click', '#lkc-accept',  function(){ acceptCall($(this).data('call-id')); });
  $(document).on('click', '#lkc-decline', function(){ declineCall($(this).data('call-id')); ringingForCallId=null; });
  $(document).on('click', '#lkc-mic',    toggleMic);
  $(document).on('click', '#lkc-cam',    toggleCam);
  $(document).on('click', '#lkc-screen', toggleScreen);
  $(document).on('click', '#lkc-hangup', hangUp);
})(jQuery);
