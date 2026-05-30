'use client';

import { FormEvent, useEffect, useMemo, useState } from 'react';
import { useAuth } from '@/src/features/auth/useAuth';
import { HttpError } from '@/src/lib/http';
import { liveService, type LiveJoinResponse, type LiveMessage, type LiveSession } from '@/src/services/api/liveService';

type LiveRoomProps = {
  session: LiveSession;
  onClose?: () => void;
};

function buildJitsiEmbedUrl(baseUrl: string, displayName: string): string {
  const params = new URLSearchParams({
    'config.prejoinPageEnabled': 'false',
    'config.disableDeepLinking': 'true',
    'config.startWithAudioMuted': 'false',
    'config.startWithVideoMuted': 'false',
    'userInfo.displayName': displayName,
    'interfaceConfig.SHOW_JITSI_WATERMARK': 'false',
    'interfaceConfig.SHOW_BRAND_WATERMARK': 'false',
  });

  return `${baseUrl}#${params.toString()}`;
}

function formatMessageTime(value: string | null): string {
  if (!value) return '';

  return new Intl.DateTimeFormat('fr-FR', {
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(value));
}

export function LiveRoom({ session, onClose }: Readonly<LiveRoomProps>) {
  const { user } = useAuth();
  const [joinData, setJoinData] = useState<LiveJoinResponse['data'] | null>(null);
  const [messages, setMessages] = useState<LiveMessage[]>([]);
  const [message, setMessage] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [isSending, setIsSending] = useState(false);

  useEffect(() => {
    let mounted = true;

    void (async () => {
      try {
        const response = await liveService.join(session.id);
        const initialMessages = await liveService.messages(session.id);

        if (!mounted) return;

        setJoinData(response.data);
        setMessages(initialMessages.data);
        setError(null);
      } catch (err) {
        if (!mounted) return;
        setError(err instanceof HttpError ? err.message : 'Impossible de rejoindre la classe virtuelle.');
      }
    })();

    return () => {
      mounted = false;
    };
  }, [session.id]);

  useEffect(() => {
    if (!joinData) return;

    const interval = window.setInterval(() => {
      const lastMessageId = messages.at(-1)?.id;
      void liveService.messages(session.id, lastMessageId).then((response) => {
        if (response.data.length === 0) return;
        setMessages((current) => {
          const knownIds = new Set(current.map((item) => item.id));
          return [...current, ...response.data.filter((item) => !knownIds.has(item.id))];
        });
      }).catch(() => undefined);
    }, 5000);

    return () => window.clearInterval(interval);
  }, [joinData, messages, session.id]);

  const iframeUrl = useMemo(() => {
    if (!joinData) return null;
    return buildJitsiEmbedUrl(joinData.room.url, user?.name ?? 'Participant Ed-Tech');
  }, [joinData, user?.name]);

  async function submitMessage(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const trimmedMessage = message.trim();
    if (!trimmedMessage) return;

    try {
      setIsSending(true);
      const response = await liveService.sendMessage(session.id, trimmedMessage);
      setMessages((current) => [...current, response.data]);
      setMessage('');
      setError(null);
    } catch (err) {
      setError(err instanceof HttpError ? err.message : 'Impossible d’envoyer le message.');
    } finally {
      setIsSending(false);
    }
  }

  return (
    <section className="live-room-shell" aria-live="polite">
      <div className="live-room-header">
        <div>
          <h2>{session.title}</h2>
          <p>{session.course?.title ?? 'Cours'} · Salon sécurisé Jitsi</p>
        </div>
        {onClose ? (
          <button className="button ghost" onClick={onClose} type="button">
            Fermer
          </button>
        ) : null}
      </div>

      {error ? <p className="error">{error}</p> : null}

      <div className="live-room-grid">
        <div className="jitsi-frame-wrap">
          {iframeUrl ? (
            <iframe
              allow="camera; microphone; fullscreen; display-capture; autoplay; clipboard-write"
              className="jitsi-frame"
              referrerPolicy="strict-origin-when-cross-origin"
              src={iframeUrl}
              title={`Classe virtuelle ${session.title}`}
            />
          ) : (
            <div className="jitsi-loading">Connexion au salon sécurisé...</div>
          )}
        </div>

        <aside className="live-chat-panel">
          <h3>Tchat de classe</h3>
          <div className="live-chat-messages">
            {messages.length === 0 ? <p className="helper">Aucun message pour le moment.</p> : null}
            {messages.map((item) => (
              <article className="live-chat-message" key={item.id}>
                <strong>{item.user?.name ?? 'Participant'}</strong>
                <span>{formatMessageTime(item.created_at)}</span>
                <p>{item.message}</p>
              </article>
            ))}
          </div>
          <form className="live-chat-form" onSubmit={submitMessage}>
            <input
              aria-label="Message de tchat"
              maxLength={1000}
              onChange={(event) => setMessage(event.target.value)}
              placeholder="Écrire un message..."
              value={message}
            />
            <button className="button primary" disabled={isSending || !message.trim()} type="submit">
              Envoyer
            </button>
          </form>
        </aside>
      </div>
    </section>
  );
}
