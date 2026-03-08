import{j as e}from"./jsx-runtime-u17CrQMm.js";import{B as p}from"./button-BpVGWhoM.js";import{C as m,a as f,b as g,c as h,d as x}from"./card-D9sG6r10.js";import{c as b}from"./utils-BQHNewu7.js";import{L as v}from"./lock-CeH_dQoX.js";import"./index-LHNt3CwB.js";import"./loader-circle-sKsh7iHY.js";import"./createLucideIcon-Cq8_ABKM.js";import"./index-CAT4JDgN.js";import"./_commonjsHelpers-CE1G-McA.js";import"./index-C5dpwcrp.js";import"./index-dmJXtalC.js";function n({hasAccess:i,title:c="Upgrade Required",description:o="This feature is not available on your current plan.",ctaLabel:d="Upgrade Plan",onUpgrade:a,children:l,className:u}){return i?e.jsx(e.Fragment,{children:l}):e.jsx("div",{className:b("flex items-center justify-center p-8",u),children:e.jsxs(m,{className:"max-w-sm w-full text-center",children:[e.jsxs(f,{className:"items-center",children:[e.jsx("div",{className:"flex size-12 items-center justify-center rounded-full bg-muted mb-2",children:e.jsx(v,{className:"size-5 text-muted-foreground"})}),e.jsx(g,{children:c}),e.jsx(h,{children:o})]}),a&&e.jsx(x,{children:e.jsx(p,{onClick:a,className:"w-full",children:d})})]})})}n.__docgenInfo={description:"",methods:[],displayName:"FeatureGate",props:{hasAccess:{required:!0,tsType:{name:"boolean"},description:""},feature:{required:!1,tsType:{name:"string"},description:""},title:{required:!1,tsType:{name:"string"},description:"",defaultValue:{value:'"Upgrade Required"',computed:!1}},description:{required:!1,tsType:{name:"string"},description:"",defaultValue:{value:'"This feature is not available on your current plan."',computed:!1}},ctaLabel:{required:!1,tsType:{name:"string"},description:"",defaultValue:{value:'"Upgrade Plan"',computed:!1}},onUpgrade:{required:!1,tsType:{name:"signature",type:"function",raw:"() => void",signature:{arguments:[],return:{name:"void"}}},description:""},children:{required:!0,tsType:{name:"ReactReactNode",raw:"React.ReactNode"},description:""},className:{required:!1,tsType:{name:"string"},description:""}}};const w={title:"SaaS/FeatureGate",component:n,tags:["autodocs"],parameters:{layout:"centered"},argTypes:{hasAccess:{control:"boolean"},title:{control:"text"},description:{control:"text"},ctaLabel:{control:"text"}}},r={args:{hasAccess:!0,children:e.jsx("div",{className:"rounded-lg border border-border bg-card p-6 text-sm text-muted-foreground",children:"Premium feature content is visible here."})}},s={args:{hasAccess:!1,title:"Advanced Analytics",description:"Upgrade to Business plan to access detailed analytics and custom reports.",ctaLabel:"Upgrade to Business",onUpgrade:()=>alert("Upgrade clicked"),children:e.jsx("div",{children:"Hidden content"})}},t={args:{hasAccess:!1,title:"Enterprise Feature",description:"Contact sales to enable this feature for your organization.",children:e.jsx("div",{children:"Hidden content"})}};r.parameters={...r.parameters,docs:{...r.parameters?.docs,source:{originalSource:`{
  args: {
    hasAccess: true,
    children: <div className="rounded-lg border border-border bg-card p-6 text-sm text-muted-foreground">
                Premium feature content is visible here.
            </div>
  }
}`,...r.parameters?.docs?.source}}};s.parameters={...s.parameters,docs:{...s.parameters?.docs,source:{originalSource:`{
  args: {
    hasAccess: false,
    title: 'Advanced Analytics',
    description: 'Upgrade to Business plan to access detailed analytics and custom reports.',
    ctaLabel: 'Upgrade to Business',
    onUpgrade: () => alert('Upgrade clicked'),
    children: <div>Hidden content</div>
  }
}`,...s.parameters?.docs?.source}}};t.parameters={...t.parameters,docs:{...t.parameters?.docs,source:{originalSource:`{
  args: {
    hasAccess: false,
    title: 'Enterprise Feature',
    description: 'Contact sales to enable this feature for your organization.',
    children: <div>Hidden content</div>
  }
}`,...t.parameters?.docs?.source}}};const H=["WithAccess","NoAccess","NoAccessNoCTA"];export{s as NoAccess,t as NoAccessNoCTA,r as WithAccess,H as __namedExportsOrder,w as default};
