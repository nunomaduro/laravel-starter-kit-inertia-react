import{j as e}from"./jsx-runtime-u17CrQMm.js";import{r as u}from"./index-CAT4JDgN.js";import{c as g}from"./utils-BQHNewu7.js";import{X as y}from"./x-if6haS7Y.js";import"./_commonjsHelpers-CE1G-McA.js";import"./createLucideIcon-Cq8_ABKM.js";function o({daysRemaining:r,onUpgrade:t,className:l,storageKey:i="trial-banner-dismissed"}){const[d,c]=u.useState(()=>typeof window>"u"?!1:localStorage.getItem(i)==="true");if(r===null||d)return null;const m=()=>{c(!0),localStorage.setItem(i,"true")},p=r<=3;return e.jsxs("div",{className:g("relative flex items-center justify-center gap-3 px-4 py-2 text-sm font-medium",p?"bg-destructive text-destructive-foreground":"bg-primary text-primary-foreground",l),children:[e.jsxs("span",{children:["Your trial ends in"," ",e.jsxs("strong",{children:[r," ",r===1?"day":"days"]}),"."," ",t&&e.jsx("button",{onClick:t,className:"underline underline-offset-2 hover:no-underline",children:"Upgrade now →"})]}),e.jsx("button",{onClick:m,"aria-label":"Dismiss trial banner",className:"absolute right-3 top-1/2 -translate-y-1/2 rounded p-1 opacity-70 hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-current",children:e.jsx(y,{className:"size-4"})})]})}o.__docgenInfo={description:"",methods:[],displayName:"TrialBanner",props:{daysRemaining:{required:!0,tsType:{name:"union",raw:"number | null",elements:[{name:"number"},{name:"null"}]},description:""},onUpgrade:{required:!1,tsType:{name:"signature",type:"function",raw:"() => void",signature:{arguments:[],return:{name:"void"}}},description:""},className:{required:!1,tsType:{name:"string"},description:""},storageKey:{required:!1,tsType:{name:"string"},description:"",defaultValue:{value:'"trial-banner-dismissed"',computed:!1}}}};const U={title:"SaaS/TrialBanner",component:o,tags:["autodocs"],parameters:{layout:"fullscreen"},argTypes:{daysRemaining:{control:{type:"range",min:0,max:30}}}},a={args:{daysRemaining:12,storageKey:"sb-trial-healthy"}},s={args:{daysRemaining:2,storageKey:"sb-trial-expiring",onUpgrade:()=>alert("Upgrade clicked")}},n={args:{daysRemaining:1,storageKey:"sb-trial-last-day",onUpgrade:()=>alert("Upgrade clicked")}};a.parameters={...a.parameters,docs:{...a.parameters?.docs,source:{originalSource:`{
  args: {
    daysRemaining: 12,
    storageKey: 'sb-trial-healthy'
  }
}`,...a.parameters?.docs?.source}}};s.parameters={...s.parameters,docs:{...s.parameters?.docs,source:{originalSource:`{
  args: {
    daysRemaining: 2,
    storageKey: 'sb-trial-expiring',
    onUpgrade: () => alert('Upgrade clicked')
  }
}`,...s.parameters?.docs?.source}}};n.parameters={...n.parameters,docs:{...n.parameters?.docs,source:{originalSource:`{
  args: {
    daysRemaining: 1,
    storageKey: 'sb-trial-last-day',
    onUpgrade: () => alert('Upgrade clicked')
  }
}`,...n.parameters?.docs?.source}}};const S=["Healthy","Expiring","LastDay"];export{s as Expiring,a as Healthy,n as LastDay,S as __namedExportsOrder,U as default};
