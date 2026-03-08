import{j as e}from"./jsx-runtime-u17CrQMm.js";import{c as S}from"./utils-BQHNewu7.js";import{S as U}from"./skeleton-CuxgNOSS.js";import"./use-reduced-motion-Bb6b8sk-.js";import"./index-CAT4JDgN.js";import"./_commonjsHelpers-CE1G-McA.js";function s({value:d,max:f=100,label:m,sublabel:p,showValue:q=!0,color:G,trackColor:N,skeleton:$=!1,size:a=200,className:b}){if($)return e.jsx(U,{className:S("rounded-full",b),style:{width:a,height:a}});const x=Math.min(Math.max(d/f,0),1),g=240,t=-120,h=a/2,v=a/2,o=a/2*.75,y=a*.08;function j(l){const r=(l-90)*Math.PI/180;return{x:h+o*Math.cos(r),y:v+o*Math.sin(r)}}function k(l,r){const w=j(l),T=j(r),L=r-l>180?1:0;return`M ${w.x} ${w.y} A ${o} ${o} 0 ${L} 1 ${T.x} ${T.y}`}const M=t+g*x,A=k(t,t+g),C=x>0?k(t,M):"",V=G??"var(--primary)",P=N??"var(--muted)";return e.jsxs("div",{className:S("flex flex-col items-center",b),children:[e.jsxs("svg",{width:a,height:a*.75,viewBox:`0 0 ${a} ${a*.75}`,"aria-valuenow":d,"aria-valuemin":0,"aria-valuemax":f,role:"meter",children:[e.jsx("path",{d:A,fill:"none",stroke:P,strokeWidth:y,strokeLinecap:"round"}),C&&e.jsx("path",{d:C,fill:"none",stroke:V,strokeWidth:y,strokeLinecap:"round"}),q&&e.jsx("text",{x:h,y:v*.9,textAnchor:"middle",dominantBaseline:"middle",fill:"var(--foreground)",fontSize:a*.16,fontWeight:600,children:d})]}),(m??p)&&e.jsxs("div",{className:"mt-1 text-center",children:[m&&e.jsx("p",{className:"text-sm font-medium text-foreground",children:m}),p&&e.jsx("p",{className:"text-xs text-muted-foreground",children:p})]})]})}s.__docgenInfo={description:"",methods:[],displayName:"GaugeChart",props:{value:{required:!0,tsType:{name:"number"},description:""},max:{required:!1,tsType:{name:"number"},description:"",defaultValue:{value:"100",computed:!1}},label:{required:!1,tsType:{name:"string"},description:""},sublabel:{required:!1,tsType:{name:"string"},description:""},showValue:{required:!1,tsType:{name:"boolean"},description:"",defaultValue:{value:"true",computed:!1}},color:{required:!1,tsType:{name:"string"},description:""},trackColor:{required:!1,tsType:{name:"string"},description:""},skeleton:{required:!1,tsType:{name:"boolean"},description:"",defaultValue:{value:"false",computed:!1}},size:{required:!1,tsType:{name:"number"},description:"",defaultValue:{value:"200",computed:!1}},className:{required:!1,tsType:{name:"string"},description:""}}};const W={title:"Charts/GaugeChart",component:s,tags:["autodocs"],parameters:{layout:"centered"},argTypes:{value:{control:{type:"range",min:0,max:100}},max:{control:{type:"number"}},showValue:{control:"boolean"},skeleton:{control:"boolean"},size:{control:{type:"range",min:80,max:300,step:20}}}},n={args:{value:72,label:"Health Score",sublabel:"Good"}},c={args:{value:23,label:"Completion",sublabel:"Needs work"}},u={args:{value:100,label:"Uptime",sublabel:"99.99%"}},i={render:()=>e.jsxs("div",{className:"flex gap-8 flex-wrap",children:[e.jsx(s,{value:92,label:"CPU",sublabel:"92%",size:150}),e.jsx(s,{value:68,label:"Memory",sublabel:"68%",size:150}),e.jsx(s,{value:34,label:"Storage",sublabel:"34%",size:150})]})};n.parameters={...n.parameters,docs:{...n.parameters?.docs,source:{originalSource:`{
  args: {
    value: 72,
    label: 'Health Score',
    sublabel: 'Good'
  }
}`,...n.parameters?.docs?.source}}};c.parameters={...c.parameters,docs:{...c.parameters?.docs,source:{originalSource:`{
  args: {
    value: 23,
    label: 'Completion',
    sublabel: 'Needs work'
  }
}`,...c.parameters?.docs?.source}}};u.parameters={...u.parameters,docs:{...u.parameters?.docs,source:{originalSource:`{
  args: {
    value: 100,
    label: 'Uptime',
    sublabel: '99.99%'
  }
}`,...u.parameters?.docs?.source}}};i.parameters={...i.parameters,docs:{...i.parameters?.docs,source:{originalSource:`{
  render: () => <div className="flex gap-8 flex-wrap">
            <GaugeChart value={92} label="CPU" sublabel="92%" size={150} />
            <GaugeChart value={68} label="Memory" sublabel="68%" size={150} />
            <GaugeChart value={34} label="Storage" sublabel="34%" size={150} />
        </div>
}`,...i.parameters?.docs?.source}}};const D=["Default","Low","Full","Grid"];export{n as Default,u as Full,i as Grid,c as Low,D as __namedExportsOrder,W as default};
