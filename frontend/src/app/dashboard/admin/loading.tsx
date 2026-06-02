export default function AdminDashboardLoading() {
  return (
    <main className="min-h-screen bg-[#F8FAFC] px-4 py-6 sm:px-6 lg:px-8">
      <div className="mx-auto grid w-full max-w-screen-2xl gap-6">
        <section className="rounded-[2rem] border border-slate-200 bg-white p-9 shadow-sm">
          <div className="h-4 w-56 animate-pulse rounded-full bg-blue-100" />
          <div className="mt-5 h-12 w-full max-w-2xl animate-pulse rounded-2xl bg-slate-100" />
          <div className="mt-4 h-5 w-full max-w-3xl animate-pulse rounded-full bg-slate-100" />
        </section>
        <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
          {Array.from({ length: 4 }).map((_, index) => (
            <div key={index} className="h-44 animate-pulse rounded-[1.75rem] border border-slate-200 bg-white shadow-sm" />
          ))}
        </section>
        <section className="h-96 animate-pulse rounded-[2rem] border border-slate-200 bg-white shadow-sm" />
      </div>
    </main>
  );
}
