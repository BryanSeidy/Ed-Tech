'use client';

import { FormEvent, useEffect, useMemo, useState } from 'react';
import Link from 'next/link';
import { useParams, useRouter } from 'next/navigation';
import { ProtectedView } from '@/src/components/auth/ProtectedView';
import { AppNav } from '@/src/components/layout/AppNav';
import { HttpError } from '@/src/lib/http';
import { courseService, type CourseSummary } from '@/src/services/api/courseService';
import { quizService, type CreateQuizPayload } from '@/src/services/api/quizService';


type AnswerForm = { answer_text: string; is_correct: boolean };
type QuestionForm = { question_text: string; answers: AnswerForm[] };

const initialQuestion = (): QuestionForm => ({
  question_text: '',
  answers: [
    { answer_text: '', is_correct: true },
    { answer_text: '', is_correct: false },
  ],
});

export default function CreateQuizPage() {
  const params = useParams<{ courseId: string }>();
  const router = useRouter();
  const courseId = Number(params.courseId);

  const [course, setCourse] = useState<CourseSummary | null>(null);
  const [lessonId, setLessonId] = useState('');
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [passingScore, setPassingScore] = useState(70);
  const [isPublished, setIsPublished] = useState(false);
  const [questions, setQuestions] = useState<QuestionForm[]>([initialQuestion()]);
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);


  useEffect(() => {
    if (!courseId) return;

    void (async () => {
      try {
        const courseData = await courseService.detail(courseId);
        setCourse(courseData);
      } catch (err) {
        setError(err instanceof HttpError ? err.message : 'Impossible de charger les leçons du cours.');
      }
    })();
  }, [courseId]);

  const availableLessons = useMemo(
    () => course?.modules?.flatMap((module) => module.lessons ?? []) ?? [],
    [course],
  );

  const canSubmit = useMemo(
    () => Boolean(lessonId && title.trim() && questions.every((question) => (
      question.question_text.trim()
      && question.answers.length >= 2
      && question.answers.some((answer) => answer.is_correct)
      && question.answers.every((answer) => answer.answer_text.trim())
    ))),
    [lessonId, title, questions],
  );

  function updateQuestion(index: number, patch: Partial<QuestionForm>) {
    setQuestions((current) => current.map((question, questionIndex) => (
      questionIndex === index ? { ...question, ...patch } : question
    )));
  }

  function updateAnswer(questionIndex: number, answerIndex: number, patch: Partial<AnswerForm>) {
    setQuestions((current) => current.map((question, currentQuestionIndex) => {
      if (currentQuestionIndex !== questionIndex) {
        return question;
      }

      return {
        ...question,
        answers: question.answers.map((answer, currentAnswerIndex) => {
          if (currentAnswerIndex !== answerIndex) {
            return patch.is_correct ? { ...answer, is_correct: false } : answer;
          }

          return { ...answer, ...patch };
        }),
      };
    }));
  }

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError(null);

    if (!canSubmit) {
      setError('Merci de compléter la leçon, le titre, chaque question et au moins deux réponses.');
      return;
    }

    const payload: CreateQuizPayload = {
      lesson_id: Number(lessonId),
      title: title.trim(),
      description: description.trim() || undefined,
      passing_score: passingScore,
      is_published: isPublished,
      questions: questions.map((question) => ({
        question_text: question.question_text.trim(),
        type: 'single_choice',
        answers: question.answers.map((answer) => ({
          answer_text: answer.answer_text.trim(),
          is_correct: answer.is_correct,
        })),
      })),
    };

    try {
      setIsSubmitting(true);
      const response = await quizService.createQuiz(payload);
      router.push(`/dashboard/courses/${courseId}/quiz/${response.data.id}`);
    } catch (err) {
      setError(err instanceof HttpError ? err.message : 'Impossible de créer le QCM.');
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <ProtectedView>
      <main className="page-shell">
        <AppNav />
        <section className="card wide-card">
          <div className="topbar">
            <div>
              <h1>Créer un QCM</h1>
              <p>Ajoutez un questionnaire bloquant associé à une leçon du cours.</p>
            </div>
            <Link className="button ghost" href={`/dashboard/courses/${courseId}`}>
              Retour au cours
            </Link>
          </div>

          <form className="form-grid" onSubmit={onSubmit} noValidate>
            <label htmlFor="lessonId">
              Leçon cible
              <select
                id="lessonId"
                onChange={(event) => setLessonId(event.target.value)}
                required
                value={lessonId}
              >
                <option value="">Sélectionner une leçon</option>
                {availableLessons.map((lesson) => (
                  <option key={lesson.id} value={lesson.id}>
                    {lesson.position}. {lesson.title}
                  </option>
                ))}
              </select>
            </label>

            <label htmlFor="title">
              Titre du QCM
              <input
                id="title"
                onChange={(event) => setTitle(event.target.value)}
                required
                value={title}
              />
            </label>

            <label htmlFor="description">
              Description
              <textarea
                id="description"
                onChange={(event) => setDescription(event.target.value)}
                rows={3}
                value={description}
              />
            </label>

            <label htmlFor="passingScore">
              Score minimal requis (%)
              <input
                id="passingScore"
                max={100}
                min={0}
                onChange={(event) => setPassingScore(Number(event.target.value))}
                required
                type="number"
                value={passingScore}
              />
            </label>

            <label className="checkbox" htmlFor="published">
              <input
                checked={isPublished}
                id="published"
                onChange={(event) => setIsPublished(event.target.checked)}
                type="checkbox"
              />
              Publier immédiatement
            </label>

            {questions.map((question, questionIndex) => (
              <article className="item-card question-editor" key={`question-${questionIndex}`}>
                <h3>Question {questionIndex + 1}</h3>
                <label htmlFor={`question-${questionIndex}`}>
                  Énoncé
                  <textarea
                    id={`question-${questionIndex}`}
                    onChange={(event) => updateQuestion(questionIndex, { question_text: event.target.value })}
                    required
                    rows={2}
                    value={question.question_text}
                  />
                </label>

                {question.answers.map((answer, answerIndex) => (
                  <div className="answer-row" key={`question-${questionIndex}-answer-${answerIndex}`}>
                    <label htmlFor={`answer-${questionIndex}-${answerIndex}`}>
                      Réponse {answerIndex + 1}
                      <input
                        id={`answer-${questionIndex}-${answerIndex}`}
                        onChange={(event) => updateAnswer(questionIndex, answerIndex, { answer_text: event.target.value })}
                        required
                        value={answer.answer_text}
                      />
                    </label>
                    <label className="checkbox" htmlFor={`correct-${questionIndex}-${answerIndex}`}>
                      <input
                        checked={answer.is_correct}
                        id={`correct-${questionIndex}-${answerIndex}`}
                        onChange={() => updateAnswer(questionIndex, answerIndex, { is_correct: true })}
                        type="radio"
                      />
                      Bonne réponse
                    </label>
                  </div>
                ))}
              </article>
            ))}

            <button className="button ghost" onClick={() => setQuestions((current) => [...current, initialQuestion()])} type="button">
              Ajouter une question
            </button>

            {error ? <p className="error">{error}</p> : null}

            <button className="button primary" disabled={!canSubmit || isSubmitting} type="submit">
              {isSubmitting ? 'Création...' : 'Créer le QCM'}
            </button>
          </form>
        </section>
      </main>
    </ProtectedView>
  );
}
