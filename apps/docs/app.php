<?php
declare(strict_types=1);
$meta['title'] = 'Documentation | Tesserarius';
?>
<?php components('nav', ['base_path' => $base_path, 'active' => 'docs']); ?>
<div class="w-[88%] max-w-6xl mx-auto mt-[10vh] mb-[10vh] flex flex-col lg:flex-row gap-8 items-start">
  <aside class="w-full lg:w-64 shrink-0 lg:sticky lg:top-20 bg-zinc-900/70 border border-zinc-800 rounded-xl p-4 flex flex-col gap-2 text-sm">
    <span class="px-2 py-1 text-xs font-bold uppercase tracking-wider text-zinc-400 instrument-serif-regular-italic">Wip Docs</span>
    <a href="#intro" class="px-2.5 py-1.5 rounded-md text-zinc-300 hover:bg-zinc-800 hover:text-[var(--accent)] transition">Introduction</a>
  </aside>

  <main class="flex-1 min-w-0 flex flex-col gap-10 text-zinc-200 leading-relaxed">
    <div id="intro" class="pb-8 scroll-mt-20 flex flex-col gap-4 border-b border-zinc-800">
      <h1 class="text-3xl sm:text-4xl font-black text-white">
        Tesserarius Documentation
      </h1>
      <p class="text-zinc-300">
        Welcome to the still a work in progress documentation of the Tesserarius, for detail about the <strong>[QED](https://github.com/Qwidio/QED)</strong> used as the baseline of this project please refer to <strong>[QED Documentation](https://github.com/Qwidio/QED/blob/main/documentation.md)</strong>
      </p>
    </div>
  </main>
</div>