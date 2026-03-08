import{j as e}from"./jsx-runtime-u17CrQMm.js";import{c as v}from"./utils-BQHNewu7.js";import{B as b}from"./badge-dtsZ3OQs.js";import{B as F}from"./button-BpVGWhoM.js";import{C as U,a as z,d as O,e as G}from"./card-D9sG6r10.js";import{r as I}from"./index-CAT4JDgN.js";import{P as M}from"./index-CBqaTKC_.js";import{Z as S}from"./zap-Bm3q3j_w.js";import{C as B}from"./check-Bwm3_tfc.js";import{c as k}from"./createLucideIcon-Cq8_ABKM.js";import"./index-yvwtsnL6.js";import"./index-dmJXtalC.js";import"./index-LHNt3CwB.js";import"./loader-circle-sKsh7iHY.js";import"./index-C5dpwcrp.js";import"./_commonjsHelpers-CE1G-McA.js";import"./index-C1GPoNty.js";import"./index-BqKNBXXg.js";const Z=[["path",{d:"M5 12h14",key:"1ays0h"}]],$=k("minus",Z);var Y="Separator",C="horizontal",D=["horizontal","vertical"],E=I.forwardRef((a,s)=>{const{decorative:i,orientation:t=C,...l}=a,n=H(t)?t:C,g=i?{role:"none"}:{"aria-orientation":n==="vertical"?n:void 0,role:"separator"};return e.jsx(M.div,{"data-orientation":n,...g,...l,ref:s})});E.displayName=Y;function H(a){return D.includes(a)}var J=E;function w({className:a,orientation:s="horizontal",decorative:i=!0,...t}){return e.jsx(J,{"data-slot":"separator",decorative:i,orientation:s,className:v("bg-border shrink-0 data-[orientation=horizontal]:h-px data-[orientation=horizontal]:w-full data-[orientation=vertical]:h-full data-[orientation=vertical]:w-px",a),...t})}w.__docgenInfo={description:"",methods:[],displayName:"Separator",props:{orientation:{defaultValue:{value:'"horizontal"',computed:!1},required:!1},decorative:{defaultValue:{value:"true",computed:!1},required:!1}}};function r({name:a,description:s,price:i,currency:t="$",billingPeriod:l="month",yearlyPrice:n,features:j,ctaLabel:g="Get started",ctaVariant:R="default",onSelect:A,isPopular:P=!1,isCurrent:c=!1,badge:h,footnote:y,className:q,disabled:V=!1,icon:T}){const L={month:"/mo",year:"/yr","one-time":" one-time"};return e.jsxs(U,{"data-slot":"pricing-card",className:v("relative flex flex-col",P&&"border-primary shadow-lg ring-1 ring-primary",c&&"border-success",q),children:[P&&!h&&e.jsx("div",{className:"absolute -top-3 left-1/2 -translate-x-1/2",children:e.jsxs(b,{className:"gap-1 px-3 py-0.5 text-xs",children:[e.jsx(S,{className:"size-3"}),"Most popular"]})}),h&&e.jsx("div",{className:"absolute -top-3 left-1/2 -translate-x-1/2",children:e.jsx(b,{variant:"secondary",className:"px-3 py-0.5 text-xs",children:h})}),c&&e.jsx("div",{className:"absolute -top-3 right-4",children:e.jsx(b,{variant:"outline",className:"border-success text-success px-3 py-0.5 text-xs",children:"Current plan"})}),e.jsxs(z,{className:"pb-4",children:[e.jsxs("div",{className:"flex items-center gap-2",children:[T&&e.jsx("div",{className:"flex size-8 items-center justify-center rounded-lg bg-primary/10 text-primary",children:T}),e.jsxs("div",{children:[e.jsx("h3",{className:"text-base font-semibold",children:a}),s&&e.jsx("p",{className:"text-xs text-muted-foreground",children:s})]})]}),e.jsxs("div",{className:"mt-3",children:[i==="custom"?e.jsx("div",{children:e.jsx("span",{className:"text-3xl font-bold",children:"Custom"})}):e.jsxs("div",{className:"flex items-end gap-1",children:[e.jsx("span",{className:"text-sm font-medium text-muted-foreground",children:t}),e.jsx("span",{className:"text-3xl font-bold leading-none",children:i}),e.jsx("span",{className:"mb-0.5 text-sm text-muted-foreground",children:L[l]})]}),n&&l==="month"&&e.jsxs("p",{className:"mt-1 text-xs text-muted-foreground",children:["or"," ",e.jsxs("span",{className:"font-medium text-foreground",children:[t,n,"/yr"]})," ","billed annually"]})]})]}),e.jsx(w,{}),e.jsx(O,{className:"flex-1 pt-4",children:e.jsx("ul",{className:"space-y-2.5",children:j.map((o,_)=>e.jsxs("li",{className:"flex items-start gap-2.5",children:[o.included!==!1?e.jsx(B,{className:"mt-px size-4 shrink-0 text-success"}):e.jsx($,{className:"mt-px size-4 shrink-0 text-muted-foreground/40"}),e.jsxs("span",{className:v("text-sm leading-tight",o.included===!1&&"text-muted-foreground"),children:[o.label,o.note&&e.jsxs("span",{className:"ml-1 text-xs text-muted-foreground",children:["(",o.note,")"]})]})]},_))})}),e.jsxs(G,{className:"flex-col gap-2 pt-0",children:[e.jsx(F,{variant:R,className:"w-full",onClick:A,disabled:V||c,children:c?"Current plan":g}),y&&e.jsx("p",{className:"text-center text-[10px] text-muted-foreground",children:y})]})]})}r.__docgenInfo={description:"",methods:[],displayName:"PricingCard",props:{name:{required:!0,tsType:{name:"string"},description:""},description:{required:!1,tsType:{name:"string"},description:""},price:{required:!0,tsType:{name:"union",raw:"number | 'custom'",elements:[{name:"number"},{name:"literal",value:"'custom'"}]},description:""},currency:{required:!1,tsType:{name:"string"},description:"",defaultValue:{value:"'$'",computed:!1}},billingPeriod:{required:!1,tsType:{name:"union",raw:"'month' | 'year' | 'one-time'",elements:[{name:"literal",value:"'month'"},{name:"literal",value:"'year'"},{name:"literal",value:"'one-time'"}]},description:"",defaultValue:{value:"'month'",computed:!1}},yearlyPrice:{required:!1,tsType:{name:"number"},description:""},features:{required:!0,tsType:{name:"Array",elements:[{name:"PricingFeature"}],raw:"PricingFeature[]"},description:""},ctaLabel:{required:!1,tsType:{name:"string"},description:"",defaultValue:{value:"'Get started'",computed:!1}},ctaVariant:{required:!1,tsType:{name:"union",raw:"'default' | 'outline'",elements:[{name:"literal",value:"'default'"},{name:"literal",value:"'outline'"}]},description:"",defaultValue:{value:"'default'",computed:!1}},onSelect:{required:!1,tsType:{name:"signature",type:"function",raw:"() => void",signature:{arguments:[],return:{name:"void"}}},description:""},isPopular:{required:!1,tsType:{name:"boolean"},description:"",defaultValue:{value:"false",computed:!1}},isCurrent:{required:!1,tsType:{name:"boolean"},description:"",defaultValue:{value:"false",computed:!1}},badge:{required:!1,tsType:{name:"string"},description:""},footnote:{required:!1,tsType:{name:"string"},description:""},className:{required:!1,tsType:{name:"string"},description:""},disabled:{required:!1,tsType:{name:"boolean"},description:"",defaultValue:{value:"false",computed:!1}},icon:{required:!1,tsType:{name:"ReactReactNode",raw:"React.ReactNode"},description:""}}};const N=[{label:"5 team members",included:!0},{label:"10 GB storage",included:!0},{label:"Basic analytics",included:!0},{label:"Email support",included:!0},{label:"Advanced reporting",included:!1},{label:"Custom domain",included:!1}],x=[{label:"Unlimited team members",included:!0},{label:"100 GB storage",included:!0},{label:"Advanced analytics",included:!0},{label:"Priority support",included:!0},{label:"Advanced reporting",included:!0},{label:"Custom domain",included:!0}],fe={title:"Composed/PricingCard",component:r,tags:["autodocs"],parameters:{layout:"centered"},argTypes:{billingPeriod:{control:"select",options:["month","year","one-time"]},isPopular:{control:"boolean"},isCurrent:{control:"boolean"},disabled:{control:"boolean"}}},d={args:{name:"Starter",description:"Perfect for small teams just getting started.",price:29,billingPeriod:"month",features:N,ctaLabel:"Get started",onSelect:()=>{}},render:a=>e.jsx("div",{className:"w-72",children:e.jsx(r,{...a})})},m={args:{name:"Pro",description:"For growing teams that need more power.",price:79,billingPeriod:"month",yearlyPrice:69,features:x,ctaLabel:"Upgrade to Pro",isPopular:!0,badge:"Most popular",icon:e.jsx(S,{className:"size-4"}),onSelect:()=>{}},render:a=>e.jsx("div",{className:"w-72",children:e.jsx(r,{...a})})},u={args:{name:"Starter",description:"Your current plan.",price:29,billingPeriod:"month",features:N,isCurrent:!0,ctaLabel:"Current plan",ctaVariant:"outline"},render:a=>e.jsx("div",{className:"w-72",children:e.jsx(r,{...a})})},p={args:{name:"Enterprise",description:"Custom solutions for large organizations.",price:"custom",features:x,ctaLabel:"Contact sales",ctaVariant:"outline"},render:a=>e.jsx("div",{className:"w-72",children:e.jsx(r,{...a})})},f={render:()=>e.jsxs("div",{className:"flex gap-4 flex-wrap justify-center",children:[e.jsx("div",{className:"w-64",children:e.jsx(r,{name:"Starter",price:29,billingPeriod:"month",features:N,ctaLabel:"Get started",isCurrent:!0,ctaVariant:"outline"})}),e.jsx("div",{className:"w-64",children:e.jsx(r,{name:"Pro",price:79,billingPeriod:"month",features:x,ctaLabel:"Upgrade",isPopular:!0,badge:"Most popular",onSelect:()=>{}})}),e.jsx("div",{className:"w-64",children:e.jsx(r,{name:"Enterprise",price:"custom",features:x,ctaLabel:"Contact sales",ctaVariant:"outline"})})]})};d.parameters={...d.parameters,docs:{...d.parameters?.docs,source:{originalSource:`{
  args: {
    name: 'Starter',
    description: 'Perfect for small teams just getting started.',
    price: 29,
    billingPeriod: 'month',
    features: STARTER_FEATURES,
    ctaLabel: 'Get started',
    onSelect: () => {}
  },
  render: args => <div className="w-72"><PricingCard {...args} /></div>
}`,...d.parameters?.docs?.source}}};m.parameters={...m.parameters,docs:{...m.parameters?.docs,source:{originalSource:`{
  args: {
    name: 'Pro',
    description: 'For growing teams that need more power.',
    price: 79,
    billingPeriod: 'month',
    yearlyPrice: 69,
    features: PRO_FEATURES,
    ctaLabel: 'Upgrade to Pro',
    isPopular: true,
    badge: 'Most popular',
    icon: <ZapIcon className="size-4" />,
    onSelect: () => {}
  },
  render: args => <div className="w-72"><PricingCard {...args} /></div>
}`,...m.parameters?.docs?.source}}};u.parameters={...u.parameters,docs:{...u.parameters?.docs,source:{originalSource:`{
  args: {
    name: 'Starter',
    description: 'Your current plan.',
    price: 29,
    billingPeriod: 'month',
    features: STARTER_FEATURES,
    isCurrent: true,
    ctaLabel: 'Current plan',
    ctaVariant: 'outline'
  },
  render: args => <div className="w-72"><PricingCard {...args} /></div>
}`,...u.parameters?.docs?.source}}};p.parameters={...p.parameters,docs:{...p.parameters?.docs,source:{originalSource:`{
  args: {
    name: 'Enterprise',
    description: 'Custom solutions for large organizations.',
    price: 'custom',
    features: PRO_FEATURES,
    ctaLabel: 'Contact sales',
    ctaVariant: 'outline'
  },
  render: args => <div className="w-72"><PricingCard {...args} /></div>
}`,...p.parameters?.docs?.source}}};f.parameters={...f.parameters,docs:{...f.parameters?.docs,source:{originalSource:`{
  render: () => <div className="flex gap-4 flex-wrap justify-center">
            <div className="w-64">
                <PricingCard name="Starter" price={29} billingPeriod="month" features={STARTER_FEATURES} ctaLabel="Get started" isCurrent ctaVariant="outline" />
            </div>
            <div className="w-64">
                <PricingCard name="Pro" price={79} billingPeriod="month" features={PRO_FEATURES} ctaLabel="Upgrade" isPopular badge="Most popular" onSelect={() => {}} />
            </div>
            <div className="w-64">
                <PricingCard name="Enterprise" price="custom" features={PRO_FEATURES} ctaLabel="Contact sales" ctaVariant="outline" />
            </div>
        </div>
}`,...f.parameters?.docs?.source}}};const xe=["Starter","Popular","Current","Enterprise","PricingGrid"];export{u as Current,p as Enterprise,m as Popular,f as PricingGrid,d as Starter,xe as __namedExportsOrder,fe as default};
