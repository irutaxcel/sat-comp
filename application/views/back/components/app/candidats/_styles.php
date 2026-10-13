<?php
defined('BASEPATH') or exit('No direct script access allowed');
/* Styles partagés du module Candidatures (préfixe « cav- »). */
?>
<style>
    /* Jetons de couleur : déclarés aussi sur les éléments placés hors de .cav
       (modales, panneau latéral, notifications), sinon var(--g) y est vide. */
    .cav,
    .cav-overlay,
    .cav-drawer,
    .cav-toasts {
        --g: #1e7b3c;
        --g-d: #155c2c;
        --g-l: #e8f5ec;
        --y: #f4c430;
        --r: #c62828;
        --r-l: #fdecea;
        --b: #1565c0;
        --b-l: #e3f0fc;
        --a: #b26a00;
        --a-l: #fff4e0;
        --tx: #1f2933;
        --mu: #6b7785;
        --bd: #e4e8ec;
        --bg: #f6f8f7;
        --card: #fff
    }

    .cav {
        padding: 22px 24px 40px;
        color: var(--tx);
        font-size: 14px
    }

    /* L'attribut hidden doit toujours gagner sur les display:flex/grid des composants */
    .cav [hidden],
    .cav-overlay [hidden],
    .cav-drawer [hidden] {
        display: none !important
    }

    .cav *,
    .cav-drawer *,
    .cav-modal * {
        box-sizing: border-box
    }

    .cav-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 16px;
        margin-bottom: 18px
    }

    .cav-crumb {
        margin: 0 0 4px;
        font-size: 12px;
        color: var(--mu);
        text-transform: uppercase;
        letter-spacing: .04em
    }

    .cav-crumb span {
        margin: 0 4px
    }

    .cav-title {
        margin: 0;
        font-size: 22px;
        font-weight: 700
    }

    .cav-sub {
        margin: 4px 0 0;
        color: var(--mu)
    }

    /* KPI */
    .cav-kpis {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 18px
    }

    .cav-kpi {
        display: flex;
        align-items: center;
        gap: 12px;
        background: var(--card);
        border: 1px solid var(--bd);
        border-radius: 12px;
        padding: 14px 16px;
        text-align: left;
        cursor: pointer;
        transition: .15s;
        font: inherit;
        color: inherit
    }

    .cav-kpi:hover {
        border-color: var(--g);
        box-shadow: 0 2px 10px rgba(30, 123, 60, .08)
    }

    .cav-kpi.is-active {
        border-color: var(--g);
        box-shadow: inset 0 0 0 1px var(--g)
    }

    .cav-kpi b {
        display: block;
        font-size: 22px;
        line-height: 1.1
    }

    .cav-kpi small {
        color: var(--mu);
        font-size: 12.5px
    }

    .cav-kpi-ico,
    .cav-big-ico {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        font-size: 17px;
        flex: none
    }

    .cav-ico-green {
        background: var(--g-l);
        color: var(--g)
    }

    .cav-ico-blue {
        background: var(--b-l);
        color: var(--b)
    }

    .cav-ico-amber {
        background: var(--a-l);
        color: var(--a)
    }

    .cav-ico-red {
        background: var(--r-l);
        color: var(--r)
    }

    /* Carte */
    .cav-card {
        background: var(--card);
        border: 1px solid var(--bd);
        border-radius: 12px;
        overflow: hidden
    }

    .cav-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--bd);
        align-items: center
    }

    .cav-search {
        position: relative;
        flex: 1 1 260px
    }

    .cav-search i {
        position: absolute;
        left: 11px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--mu);
        font-size: 13px
    }

    .cav-search input {
        padding-left: 32px !important;
        width: 100%
    }

    .cav-filters input,
    .cav-filters select,
    .cav-modal textarea {
        height: 36px;
        border: 1px solid var(--bd);
        border-radius: 8px;
        padding: 0 10px;
        font: inherit;
        color: inherit;
        background: #fff;
        outline: none
    }

    .cav-filters input:focus,
    .cav-filters select:focus,
    .cav-modal textarea:focus {
        border-color: var(--g);
        box-shadow: 0 0 0 3px var(--g-l)
    }

    .cav-bulk {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 10px 16px;
        background: var(--g-l);
        border-bottom: 1px solid #cfe7d7
    }

    .cav-bulk[hidden] {
        display: none
    }

    .cav-bulk>div {
        display: flex;
        gap: 8px;
        align-items: center
    }

    .cav-table-wrap {
        overflow-x: auto
    }

    .cav-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 860px
    }

    .cav-table th {
        background: #fafbfa;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: var(--mu);
        font-weight: 600;
        text-align: left;
        padding: 10px 12px;
        border-bottom: 1px solid var(--bd);
        white-space: nowrap
    }

    .cav-table td {
        padding: 12px;
        border-bottom: 1px solid var(--bd);
        vertical-align: middle
    }

    .cav-table tbody tr {
        transition: background .12s
    }

    .cav-table tbody tr:hover {
        background: #fafcfb
    }

    .cav-table tbody tr.is-sel {
        background: #f1f9f4
    }

    .cav-table tr.is-out {
        opacity: 0;
        transform: translateX(12px);
        transition: .3s
    }

    .cav-col-chk {
        width: 40px
    }

    .cav-col-act {
        width: 150px;
        text-align: right !important
    }

    .cav-table input[type=checkbox] {
        width: 16px;
        height: 16px;
        accent-color: var(--g);
        cursor: pointer
    }

    .cav-who {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0
    }

    .cav-av {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--g);
        color: #fff;
        display: grid;
        place-items: center;
        font-weight: 700;
        font-size: 13px;
        flex: none
    }

    .cav-who b {
        display: block;
        font-weight: 600;
        cursor: pointer
    }

    .cav-who b:hover {
        color: var(--g)
    }

    .cav-who small,
    .cav-cell-sub {
        display: block;
        color: var(--mu);
        font-size: 12.5px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 240px
    }

    .cav-tag {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap
    }

    .cav-tag-ind {
        background: #eef1f4;
        color: #4a5560
    }

    .cav-tag-eq {
        background: var(--b-l);
        color: var(--b)
    }

    .cav-tag-ok {
        background: var(--g-l);
        color: var(--g)
    }

    .cav-tag-ko {
        background: var(--a-l);
        color: var(--a)
    }

    .cav-tag-late {
        background: var(--r-l);
        color: var(--r)
    }

    .cav-acts {
        display: flex;
        justify-content: flex-end;
        gap: 6px
    }

    .cav-ibtn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid var(--bd);
        background: #fff;
        cursor: pointer;
        display: grid;
        place-items: center;
        color: var(--mu);
        transition: .12s
    }

    .cav-ibtn:hover {
        color: var(--tx);
        border-color: #c8d0d8
    }

    .cav-ibtn-ok:hover {
        background: var(--g);
        border-color: var(--g);
        color: #fff
    }

    .cav-ibtn-ko:hover {
        background: var(--r);
        border-color: var(--r);
        color: #fff
    }

    .cav-state {
        text-align: center;
        color: var(--mu);
        padding: 46px 12px !important
    }

    .cav-empty i {
        font-size: 34px;
        color: var(--g);
        margin-bottom: 10px;
        display: block
    }

    .cav-empty b {
        display: block;
        color: var(--tx);
        font-size: 15px;
        margin-bottom: 4px
    }

    .cav-foot {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        color: var(--mu);
        font-size: 13px;
        gap: 10px;
        flex-wrap: wrap
    }

    .cav-pager {
        display: flex;
        gap: 4px
    }

    .cav-pager button {
        min-width: 32px;
        height: 32px;
        border: 1px solid var(--bd);
        background: #fff;
        border-radius: 8px;
        cursor: pointer;
        font: inherit;
        color: inherit
    }

    .cav-pager button.is-active {
        background: var(--g);
        border-color: var(--g);
        color: #fff
    }

    .cav-pager button:disabled {
        opacity: .45;
        cursor: default
    }

    /* Boutons */
    .cav-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        height: 36px;
        padding: 0 14px;
        border-radius: 8px;
        border: 1px solid transparent;
        font: inherit;
        font-weight: 600;
        cursor: pointer;
        transition: .12s;
        white-space: nowrap
    }

    .cav-btn:disabled {
        opacity: .6;
        cursor: wait
    }

    .cav-btn-success {
        background: var(--g);
        color: #fff
    }

    .cav-btn-success:hover {
        background: var(--g-d)
    }

    .cav-btn-danger {
        background: var(--r);
        color: #fff
    }

    .cav-btn-danger:hover {
        background: #a31f1f
    }

    .cav-btn-danger-o {
        background: #fff;
        color: var(--r);
        border-color: #f0c4c4
    }

    .cav-btn-danger-o:hover {
        background: var(--r-l)
    }

    .cav-btn-ghost {
        background: #fff;
        color: var(--tx);
        border-color: var(--bd)
    }

    .cav-btn-ghost:hover {
        border-color: #c8d0d8
    }

    .cav-link {
        background: none;
        border: 0;
        color: var(--mu);
        cursor: pointer;
        font: inherit;
        text-decoration: underline;
        padding: 0 4px
    }

    .cav-link:hover {
        color: var(--tx)
    }

    /* Overlays */
    .cav-overlay {
        position: fixed;
        inset: 0;
        background: rgba(17, 24, 39, .42);
        z-index: 1050
    }

    .cav-overlay[hidden] {
        display: none
    }

    .cav-overlay-modal {
        display: grid;
        place-items: center;
        padding: 16px
    }

    .cav-overlay-modal[hidden] {
        display: none
    }

    .cav-drawer {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        width: min(460px, 100%);
        background: #fff;
        z-index: 1060;
        transform: translateX(100%);
        transition: transform .25s ease;
        display: flex;
        flex-direction: column;
        box-shadow: -8px 0 30px rgba(0, 0, 0, .12);
        color: #1f2933;
        font-size: 14px
    }

    .cav-drawer.is-open {
        transform: none
    }

    .cav-drawer-head,
    .cav-modal-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #e4e8ec
    }

    .cav-drawer-head h3,
    .cav-modal-head h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 700
    }

    .cav-drawer-body {
        padding: 20px;
        overflow-y: auto;
        flex: 1
    }

    .cav-drawer-foot {
        padding: 14px 20px;
        border-top: 1px solid #e4e8ec;
        display: flex;
        gap: 8px;
        justify-content: flex-end
    }

    .cav-x {
        background: none;
        border: 0;
        font-size: 24px;
        line-height: 1;
        cursor: pointer;
        color: #6b7785
    }

    .cav-d-id {
        display: flex;
        gap: 14px;
        align-items: center;
        margin-bottom: 18px
    }

    .cav-d-id .cav-av {
        width: 52px;
        height: 52px;
        font-size: 17px
    }

    .cav-d-id h4 {
        margin: 0 0 4px;
        font-size: 17px
    }

    .cav-d-sec {
        margin: 18px 0 8px;
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #6b7785;
        font-weight: 700
    }

    .cav-dl {
        display: grid;
        grid-template-columns: 130px 1fr;
        gap: 8px 12px;
        margin: 0
    }

    .cav-dl dt {
        color: #6b7785
    }

    .cav-dl dd {
        margin: 0;
        word-break: break-word
    }

    .cav-member {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 12px;
        border: 1px solid #e4e8ec;
        border-radius: 10px;
        margin-bottom: 8px
    }

    .cav-member small {
        display: block;
        color: #6b7785
    }

    .cav-alert {
        padding: 10px 12px;
        border-radius: 10px;
        font-size: 13px;
        margin-bottom: 12px;
        display: flex;
        gap: 8px
    }

    .cav-alert-warn {
        background: #fff4e0;
        color: #8a5200
    }

    .cav-alert-red {
        background: #fdecea;
        color: #a31f1f
    }

    .cav-modal {
        background: #fff;
        border-radius: 14px;
        width: min(540px, 100%);
        box-shadow: 0 20px 50px rgba(0, 0, 0, .2);
        color: #1f2933;
        font-size: 14px;
        animation: cavPop .18s ease
    }

    .cav-modal-sm {
        width: min(400px, 100%)
    }

    .cav-modal-body {
        padding: 18px 20px
    }

    .cav-modal-foot {
        padding: 14px 20px;
        border-top: 1px solid #e4e8ec;
        display: flex;
        justify-content: flex-end;
        gap: 8px
    }

    .cav-modal textarea {
        width: 100%;
        height: auto;
        padding: 10px;
        resize: vertical;
        display: block;
        margin-bottom: 4px
    }

    .cav-center {
        text-align: center
    }

    .cav-center h3 {
        margin: 12px 0 6px;
        font-size: 17px
    }

    .cav-big-ico {
        margin: 0 auto;
        width: 54px;
        height: 54px;
        font-size: 22px;
        border-radius: 50%
    }

    .cav-label {
        display: block;
        font-weight: 600;
        margin: 12px 0 6px
    }

    .cav-req {
        color: #c62828
    }

    .cav-muted {
        color: #6b7785
    }

    .cav-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 6px
    }

    .cav-chip {
        border: 1px solid #e4e8ec;
        background: #fafbfa;
        border-radius: 999px;
        padding: 5px 11px;
        font-size: 12.5px;
        cursor: pointer;
        font: inherit;
        font-size: 12.5px;
        text-align: left
    }

    .cav-chip:hover {
        border-color: #c62828;
        color: #c62828
    }

    @keyframes cavPop {
        from {
            transform: scale(.96);
            opacity: 0
        }

        to {
            transform: none;
            opacity: 1
        }
    }

    .cav-toasts {
        position: fixed;
        right: 20px;
        bottom: 20px;
        z-index: 1080;
        display: flex;
        flex-direction: column;
        gap: 8px
    }

    .cav-toast {
        background: #1f2933;
        color: #fff;
        padding: 11px 16px;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .2);
        font-size: 13.5px;
        max-width: 360px;
        display: flex;
        gap: 9px;
        align-items: flex-start;
        animation: cavPop .2s ease
    }

    .cav-toast.ok i {
        color: #6fdc8c
    }

    .cav-toast.err i {
        color: #ff8a80
    }

    @media (max-width:992px) {
        .cav-kpis {
            grid-template-columns: repeat(2, 1fr)
        }
    }

    @media (max-width:576px) {
        .cav {
            padding: 16px 12px 30px
        }

        .cav-head {
            flex-direction: column;
            align-items: flex-start
        }

        .cav-kpis {
            gap: 10px
        }

        .cav-kpi {
            padding: 12px
        }

        .cav-filters select {
            flex: 1 1 45%
        }

        .cav-bulk {
            flex-direction: column;
            align-items: flex-start
        }
    }

    /* ---------- Ajouts « Candidats & équipes » ---------- */
    .cav-kpi em {
        display: block;
        font-style: normal;
        font-size: 11.5px;
        color: var(--mu);
        margin-top: 2px
    }

    .cav-kpi {
        cursor: default
    }

    .cav-kpi[data-kpi] {
        cursor: pointer
    }

    .cav-ico-grey {
        background: #eef1f4;
        color: #4a5560
    }

    .cav-tabs {
        display: flex;
        gap: 4px;
        padding: 10px 12px 0;
        border-bottom: 1px solid var(--bd);
        overflow-x: auto
    }

    .cav-tab {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 14px;
        border: 0;
        background: none;
        font: inherit;
        font-weight: 600;
        color: var(--mu);
        cursor: pointer;
        border-bottom: 2px solid transparent;
        margin-bottom: -1px;
        white-space: nowrap
    }

    .cav-tab:hover {
        color: var(--tx)
    }

    .cav-tab.is-active {
        color: var(--g);
        border-bottom-color: var(--g)
    }

    .cav-tab .cav-cnt {
        min-width: 22px;
        padding: 1px 7px;
        border-radius: 999px;
        background: #eef1f4;
        color: #4a5560;
        font-size: 11.5px;
        text-align: center
    }

    .cav-tab.is-active .cav-cnt {
        background: var(--g);
        color: #fff
    }

    .cav-st {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap
    }

    .cav-st::before {
        content: "";
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor
    }

    .cav-st-valide {
        background: #e8f5ec;
        color: #1e7b3c
    }

    .cav-st-en_attente {
        background: #fff4e0;
        color: #b26a00
    }

    .cav-st-rejete {
        background: #fdecea;
        color: #c62828
    }

    .cav-st-suspendu {
        background: #eef1f4;
        color: #4a5560
    }

    .cav-av-suspendu,
    .cav-av-rejete {
        background: #9aa5b1
    }

    .cav-ibtn-warn:hover {
        background: #4a5560;
        border-color: #4a5560;
        color: #fff
    }

    .cav-bulk-info {
        color: var(--mu);
        font-size: 13px
    }

    .cav-tl {
        list-style: none;
        margin: 0;
        padding: 0 0 0 14px;
        border-left: 2px solid #e4e8ec
    }

    .cav-tl li {
        position: relative;
        padding: 0 0 14px 14px
    }

    .cav-tl li::before {
        content: "";
        position: absolute;
        left: -21px;
        top: 4px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #9aa5b1
    }

    .cav-tl li.d-valide::before {
        border-color: #1e7b3c
    }

    .cav-tl li.d-rejete::before {
        border-color: #c62828
    }

    .cav-tl li.d-suspendu::before {
        border-color: #4a5560
    }

    .cav-tl li.d-reouvert::before {
        border-color: #b26a00
    }

    .cav-tl b {
        display: block;
        font-size: 13.5px
    }

    .cav-tl small {
        color: #6b7785
    }

    .cav-tl p {
        margin: 4px 0 0;
        font-size: 13px;
        background: #fafbfa;
        border-radius: 8px;
        padding: 7px 10px
    }

    a.cav-btn {
        text-decoration: none
    }
</style>