<div class="min-h-screen bg-cover bg-center flex flex-col sm:justify-center items-center pt-6 sm:pt-0"
     style="background-image: url('/images/2ndfloor.png');">

   <!-- Dark overlay --> 
    <div class="absolute inset-0 bg-black/40">
   </div>

     <div class="relative z-10" >
        {{ $logo }}
    </div>

    <div class="w-full sm:max-w-md mt-6 px-8 py-6 bg-black/80 text-white shadow-2xl sm:rounded-lg backdrop-blur">
        <div class="text-white">
            {{ $slot }}
        </div>
    </div>
</div>
<style>
    /* Make all labels white */
    label {
        color: #fff !important;
    }

    /* Make input text white */
    input {
        color: #000000 !important;
    }

    /* Make placeholder text white */
    input::placeholder {
        color: #fff !important;
    }

    /* Make the "Forgot your password?" link white */
    a {
        color: #fff !important;
    }
    /* Remember me label white */   
    label span {
        color: #fff !important;
    }
</style>
