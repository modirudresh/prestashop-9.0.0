{if $custom_top_promo_message}

<style>
    .top-promo {
        width: 100%;
        min-height: auto;
        background: linear-gradient(5deg, {$custom_color|escape:'htmlall':'UTF-8'}, #111);
        color: {$custom_text_color|default:'#ffffff'|escape:'htmlall':'UTF-8'};
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        position: relative;
        overflow: visible;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        transition: margin-top 0.3s ease;
        z-index: 999999;
    }

    .top-promo .promo-content {
        display: flex;
        flex-wrap: nowrap;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem 1rem;
        overflow-x: auto;
    }

    .top-promo .text-block {
        flex: 1 1 auto;
        min-width: 0;
        margin-right: 1rem;
    }

   .top-promo h3 {
    font-size: clamp(1.2rem, 2.5vw, 2rem);
    margin: 0 0 0.3rem;
    font-weight: 700;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4);
    white-space: nowrap;
  }

      .top-promo h3 strong {
          color: #ffd700;
          text-shadow: 0 0 5px rgba(255, 215, 0, 0.6);
      }

    
  .top-promo h5 {
      font-size: clamp(1rem, 2vw, 1.25rem); /* scales based on screen width */
      margin: 0;
      font-weight: 400;
      color: #f8f8f8;
      white-space: nowrap;
  }

      .top-promo p {
      font-size: clamp(0.9rem, 1.8vw, 1.1rem);
      margin-top: 0.5rem;
      color: rgba(255, 255, 255, 0.85);
  }

    .top-promo .button {
        flex-shrink: 0;
        background: #ffd700;
        color: #000;
        font-size: clamp(0.9rem, 1.5vw, 1.1rem);
        padding: 0.75rem 1.25rem;
        border-radius: 4px;
        text-decoration: none;
        font-weight: bold;
        white-space: nowrap;
        transition: background 0.3s ease;
    }

    .top-promo .button:hover {
        background: #ffc400;
    }

    .top-promo .message {
        text-align: center;
        background: rgba(0, 0, 0, 0.1);
        padding: 0.75rem 1rem;
        color: #fff;
        position: relative;
        overflow: hidden;
        font-size: clamp(1rem, 2vw, 1.3rem);
        min-height: 60px;
    }

    .top-promo .message p {
        position: absolute;
        white-space: nowrap;
        text-decoration: none;
        will-change: transform;
        animation: scroll-left 15s linear infinite;
        color: #ffd700;
    }

    .top-promo .message p:hover {
        color: #fff;
    }

    .top-promo .message p::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.2);
        z-index: -1;
    }

    .top-promo.open .message a {
        animation-play-state: paused;
    }

    @keyframes scroll-left {
        0% {
            transform: translateX(100%);
        }
        100% {
            transform: translateX(-100%);
        }
    }

    .top-promo .open-handle {
        position: absolute;
        bottom: -15px;
        left: 50%;
        transform: translateX(-50%);
        width: 45px;
        height: 35px;
        border-radius: 50%;
        cursor: pointer;
        z-index: 99999;
        transition: background 0.3s ease;
    }

    .top-promo .open-handle::after {
        content: "";
        position: absolute;
        top: 13px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 7px solid transparent;
        border-right: 7px solid transparent;
        border-top: 7px solid #fff;
        transition: transform 0.3s ease;
    }

    .top-promo.open .open-handle::after {
        transform: translateX(-50%) rotate(180deg);
    }

    .top-promo .close-button {
        position: absolute;
        top: 8px;
        right: 15px;
        background: none;
        border: none;
        color: #fff;
        font-size: 32px;
    }

  .top-promo .close-button:hover {
          position: absolute;
          top: 8px;
          right: 15px;
          background: none;
          border: none;
          color: #ffd;
          text-shadow: 0px -5px 15px rgba(255, 215, 10,6);
          font-size: 32px;
          cursor: pointer;
      }

    @media (max-width: 768px) {
        .top-promo .promo-content {
            flex-wrap: nowrap;
            overflow-x: auto;
        }

        .top-promo .text-block {
            min-width: 60%;
        }

        .top-promo .button {
            white-space: nowrap;
            flex-shrink: 0;
        }
    }
</style>

<div class="top-promo" id="topPromo">
    <button class="close-button" onclick="document.getElementById('topPromo').style.display='none';">&times;</button>

    <div class="promo-content">
        <div class="text-block">
            <h5>{$custom_top_promo_message|escape:'htmlall':'UTF-8'}</h5>
            <h3>Get <strong>30%</strong> off!</h3>

            {if isset($custom_top_promo_description) && $custom_top_promo_description}
                <p>{$custom_top_promo_description|escape:'htmlall':'UTF-8'}</p>
            {/if}
        </div>

        <a href="{$custom_top_promo_link|default:'#'|escape:'htmlall':'UTF-8'}" class="button">Learn More</a>
    </div>

    <div class="message">
        <p>{$custom_top_promo_message|escape:'htmlall':'UTF-8'}</p>
        <button class="close-button" id="promoClose">&times;</button>
    </div>

    <div class="open-handle" id="promoToggle"></div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="{$module_dir}views/js/custometoppromo.js"></script>
{/if}
