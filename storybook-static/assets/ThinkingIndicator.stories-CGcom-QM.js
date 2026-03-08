import{j as e}from"./jsx-runtime-u17CrQMm.js";import{c as n}from"./utils-BQHNewu7.js";import{u as m}from"./use-reduced-motion-Bb6b8sk-.js";import"./index-CAT4JDgN.js";import"./_commonjsHelpers-CE1G-McA.js";function d({variant:a="dots",label:c,className:p}){const s=m();return e.jsxs("div",{role:"status","aria-label":c??"Thinking…",className:n("inline-flex items-center gap-2",p),children:[a==="dots"&&e.jsx("span",{className:"flex items-center gap-1",children:[0,1,2].map(r=>e.jsx("span",{className:n("size-2 rounded-full bg-current opacity-70",!s&&"animate-bounce"),style:s?void 0:{animationDelay:`${r*150}ms`,animationDuration:"0.9s"}},r))}),a==="pulse"&&e.jsx("span",{className:n("size-3 rounded-full bg-current opacity-70",!s&&"animate-pulse")}),a==="bars"&&e.jsx("span",{className:"flex items-end gap-0.5",children:[0,1,2].map(r=>e.jsx("span",{className:n("w-1 rounded-full bg-current opacity-70",s?"h-3":"animate-[thinkBar_0.8s_ease-in-out_infinite_alternate]"),style:s?void 0:{animationDelay:`${r*120}ms`,height:"12px"}},r))}),c&&e.jsx("span",{className:"text-xs text-muted-foreground",children:c})]})}d.__docgenInfo={description:"Three-variant thinking / loading indicator for AI responses.\nRespects `prefers-reduced-motion` — animations are disabled when requested.\n\nVariants:\n - `dots`  — three bouncing dots\n - `pulse` — single pulsing circle\n - `bars`  — three animated vertical bars",methods:[],displayName:"ThinkingIndicator",props:{variant:{required:!1,tsType:{name:"union",raw:"'dots' | 'pulse' | 'bars'",elements:[{name:"literal",value:"'dots'"},{name:"literal",value:"'pulse'"},{name:"literal",value:"'bars'"}]},description:"Visual style of the indicator.",defaultValue:{value:"'dots'",computed:!1}},label:{required:!1,tsType:{name:"string"},description:"Optional label rendered next to the indicator."},className:{required:!1,tsType:{name:"string"},description:""}}};const v={title:"AI/ThinkingIndicator",component:d,tags:["autodocs"],parameters:{layout:"centered"},argTypes:{variant:{control:"select",options:["dots","pulse","bars"]},label:{control:"text"}}},t={args:{variant:"dots",label:"Thinking…"}},i={args:{variant:"pulse",label:"Processing…"}},o={args:{variant:"bars",label:"Generating…"}},l={render:()=>e.jsx("div",{className:"flex flex-col gap-6",children:["dots","pulse","bars"].map(a=>e.jsxs("div",{className:"flex items-center gap-3",children:[e.jsx(d,{variant:a}),e.jsx("span",{className:"text-sm text-muted-foreground capitalize",children:a})]},a))})};t.parameters={...t.parameters,docs:{...t.parameters?.docs,source:{originalSource:`{
  args: {
    variant: 'dots',
    label: 'Thinking…'
  }
}`,...t.parameters?.docs?.source}}};i.parameters={...i.parameters,docs:{...i.parameters?.docs,source:{originalSource:`{
  args: {
    variant: 'pulse',
    label: 'Processing…'
  }
}`,...i.parameters?.docs?.source}}};o.parameters={...o.parameters,docs:{...o.parameters?.docs,source:{originalSource:`{
  args: {
    variant: 'bars',
    label: 'Generating…'
  }
}`,...o.parameters?.docs?.source}}};l.parameters={...l.parameters,docs:{...l.parameters?.docs,source:{originalSource:`{
  render: () => <div className="flex flex-col gap-6">
            {(['dots', 'pulse', 'bars'] as const).map(v => <div key={v} className="flex items-center gap-3">
                    <ThinkingIndicator variant={v} />
                    <span className="text-sm text-muted-foreground capitalize">{v}</span>
                </div>)}
        </div>
}`,...l.parameters?.docs?.source}}};const b=["Dots","Pulse","Bars","AllVariants"];export{l as AllVariants,o as Bars,t as Dots,i as Pulse,b as __namedExportsOrder,v as default};
