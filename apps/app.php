<?php
declare(strict_types=1);
$meta['title'] = 'Home / Tesserarius';
?>
<div class="relative w-full min-h-screen flex flex-col lg:flex-row blurbg z-[2]">
    <div class="relative p-[15px] w-full lg:w-[40%] min-h-screen flex flex-col blurbg bg-[#05b8ff1e] z-[3]">
        <h1 class="mb-5 pt-[15px] translate-y-[5px] px-[25px] text-[calc(35px+0.5vw+0.5vh)] font-extrabold [-webkit-text-fill-color:transparent] [-webkit-text-stroke:1px_white]">
            TESSERARIUS
        </h1>
        <div class="relative py-[10px] px-[25px] flex flex-col">
            <h2 class="text-[20px] font-bold">BROWSE</h2>
            <a href="<?= e($base_path) ?>/library" class="link-cover hover:bg-white/25" tabindex="1">.</a>
        </div>
        <div class="relative py-[10px] px-[25px] flex flex-col">
            <h2 class="text-[20px] font-bold">CATEGORY</h2>
            <a href="<?= e($base_path) ?>/library/category" class="link-cover hover:bg-white/25" tabindex="2">.</a>
        </div>
        <div class="relative py-[10px] px-[25px] flex flex-col">
            <h2 class="text-[20px] font-bold">FORUM</h2>
            <a href="<?= e($base_path) ?>/forum" class="link-cover hover:bg-white/25" tabindex="3">.</a>
        </div>
        <div class="relative py-[10px] px-[25px] flex flex-col">
            <h2 class="text-[20px] font-bold">DOCS</h2>
            <a href="<?= e($base_path) ?>/docs" class="link-cover hover:bg-white/25" tabindex="4">.</a>
        </div>
        <div class="relative mt-auto py-[15px] px-[25px] flex flex-col">
            <p class="relative px-[15px] py-[10px] text-center text-[15px] bg-[#05b8ff1e] border border-[rgb(0,92,167)] hover:scale-105 hover:bg-[rgb(153,184,250)] hover:text-black transition-all duration-200 overflow-hidden rounded-[5px]" tabindex="5">
                LOGIN
                <a href="<?= e($base_path) ?>/auth/login" class="link-cover">.</a>
            </p>
        </div>
    </div>
</div>